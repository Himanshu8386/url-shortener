<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\Invitation;
use Illuminate\Support\Facades\Hash;

class UrlShortenerTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdminRole;
    protected $adminRole;
    protected $memberRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::create(['name' => 'SuperAdmin', 'slug' => 'superadmin']);
        $this->adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->memberRole = Role::create(['name' => 'Member', 'slug' => 'member']);
    }

    private function createSuperAdmin()
    {
        $user = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);
        UserRole::create(['user_id' => $user->id, 'role_id' => $this->superAdminRole->id]);
        return $user;
    }

    private function createCompanyAdmin($companyName = 'Company A', $email = 'admin@companya.com')
    {
        $company = Company::create(['name' => $companyName, 'status' => 1]);
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Admin User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);
        UserRole::create(['user_id' => $user->id, 'role_id' => $this->adminRole->id]);
        return [$company, $user];
    }

    private function createCompanyMember($company, $email = 'member@test.com')
    {
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Member User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);
        UserRole::create(['user_id' => $user->id, 'role_id' => $this->memberRole->id]);
        return $user;
    }

    public function test_admin_and_member_can_create_short_urls()
    {
        [$company, $admin] = $this->createCompanyAdmin();
        $member = $this->createCompanyMember($company);

        $responseAdmin = $this->actingAs($admin)->post('/urls', [
            'original_url' => 'https://laravel.com',
        ]);
        $responseAdmin->assertSessionHas('success');
        $this->assertDatabaseHas('short_urls', [
            'company_id' => $company->id,
            'user_id' => $admin->id,
            'original_url' => 'https://laravel.com',
        ]);

        $responseMember = $this->actingAs($member)->post('/urls', [
            'original_url' => 'https://github.com',
        ]);
        $responseMember->assertSessionHas('success');
        $this->assertDatabaseHas('short_urls', [
            'company_id' => $company->id,
            'user_id' => $member->id,
            'original_url' => 'https://github.com',
        ]);
    }

    public function test_super_admin_cannot_create_short_urls()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->post('/urls', [
            'original_url' => 'https://google.com',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_admin_can_only_see_short_urls_created_in_their_own_company()
    {
        [$companyA, $adminA] = $this->createCompanyAdmin('Company A', 'admin@companya.com');
        $memberA = $this->createCompanyMember($companyA, 'member@companya.com');

        [$companyB, $adminB] = $this->createCompanyAdmin('Company B', 'admin@companyb.com');

        $urlA1 = ShortUrl::create([
            'company_id' => $companyA->id,
            'user_id' => $adminA->id,
            'original_url' => 'https://companya.com/url1',
            'short_code' => 'codeA1',
            'clicks_count' => 0,
        ]);

        $urlA2 = ShortUrl::create([
            'company_id' => $companyA->id,
            'user_id' => $memberA->id,
            'original_url' => 'https://companya.com/url2',
            'short_code' => 'codeA2',
            'clicks_count' => 0,
        ]);

        $urlB = ShortUrl::create([
            'company_id' => $companyB->id,
            'user_id' => $adminB->id,
            'original_url' => 'https://companyb.com/secret',
            'short_code' => 'codeB1',
            'clicks_count' => 0,
        ]);

        $response = $this->actingAs($adminA)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('codeA1');
        $response->assertSee('codeA2');
        $response->assertDontSee('codeB1');
    }

    public function test_member_can_only_see_short_urls_created_by_themselves()
    {
        [$company, $admin] = $this->createCompanyAdmin('Company Tech', 'admin@tech.com');
        $member1 = $this->createCompanyMember($company, 'member1@tech.com');
        $member2 = $this->createCompanyMember($company, 'member2@tech.com');

        $adminUrl = ShortUrl::create([
            'company_id' => $company->id,
            'user_id' => $admin->id,
            'original_url' => 'https://admin-link.com',
            'short_code' => 'adm001',
            'clicks_count' => 0,
        ]);

        $member1Url = ShortUrl::create([
            'company_id' => $company->id,
            'user_id' => $member1->id,
            'original_url' => 'https://member1-link.com',
            'short_code' => 'mem001',
            'clicks_count' => 0,
        ]);

        $member2Url = ShortUrl::create([
            'company_id' => $company->id,
            'user_id' => $member2->id,
            'original_url' => 'https://member2-link.com',
            'short_code' => 'mem002',
            'clicks_count' => 0,
        ]);

        $response = $this->actingAs($member1)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('mem001');
        $response->assertDontSee('adm001');
        $response->assertDontSee('mem002');
    }

    public function test_short_urls_are_publicly_resolvable_and_redirect_to_original_url()
    {
        [$company, $admin] = $this->createCompanyAdmin();

        $shortUrl = ShortUrl::create([
            'company_id' => $company->id,
            'user_id' => $admin->id,
            'original_url' => 'https://example.com/target-destination',
            'short_code' => 'go-target',
            'clicks_count' => 0,
        ]);

        $response = $this->get('/go-target');

        $response->assertRedirect('https://example.com/target-destination');

        $shortUrl->refresh();
        $this->assertEquals(1, $shortUrl->clicks_count);

        $this->assertDatabaseHas('url_clicks', [
            'short_url_id' => $shortUrl->id,
        ]);
    }

    public function test_super_admin_can_see_short_urls_for_every_company()
    {
        $superAdmin = $this->createSuperAdmin();

        [$companyA, $adminA] = $this->createCompanyAdmin('Client A', 'a@clienta.com');
        [$companyB, $adminB] = $this->createCompanyAdmin('Client B', 'b@clientb.com');

        ShortUrl::create([
            'company_id' => $companyA->id,
            'user_id' => $adminA->id,
            'original_url' => 'https://site-a.com',
            'short_code' => 'code-all-a',
            'clicks_count' => 0,
        ]);

        ShortUrl::create([
            'company_id' => $companyB->id,
            'user_id' => $adminB->id,
            'original_url' => 'https://site-b.com',
            'short_code' => 'code-all-b',
            'clicks_count' => 0,
        ]);

        $response = $this->actingAs($superAdmin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('code-all-a');
        $response->assertSee('code-all-b');
        $response->assertSee('Client A');
        $response->assertSee('Client B');
    }

    public function test_super_admin_can_invite_admin_in_a_new_company()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->post('/invitations/client', [
            'client_name' => 'New Enterprise Corp',
            'email' => 'newadmin@enterprise.com',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('companies', [
            'name' => 'New Enterprise Corp',
        ]);

        $this->assertDatabaseHas('invitations', [
            'email' => 'newadmin@enterprise.com',
            'role' => 'Admin',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_invite_member_in_their_own_company()
    {
        [$company, $admin] = $this->createCompanyAdmin();

        $response = $this->actingAs($admin)->post('/invitations/member', [
            'name' => 'New Teammate',
            'email' => 'teammate@companya.com',
            'role' => 'Member',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('invitations', [
            'company_id' => $company->id,
            'email' => 'teammate@companya.com',
            'role' => 'Member',
            'status' => 'pending',
        ]);
    }
}
