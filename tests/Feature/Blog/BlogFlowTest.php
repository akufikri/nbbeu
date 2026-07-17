<?php

namespace Tests\Feature\Blog;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_home_page_shows_published_posts(): void
    {
        $author = User::factory()->create();
        Post::create([
            'author_id' => $author->id,
            'title' => 'Contoh Berita',
            'slug' => 'contoh-berita',
            'content' => '<p>Isi berita</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('home'))->assertOk()->assertSee('Contoh Berita');
    }

    public function test_blog_index_only_shows_published_posts(): void
    {
        $author = User::factory()->create();
        Post::create([
            'author_id' => $author->id,
            'title' => 'Sudah Terbit',
            'slug' => 'sudah-terbit',
            'content' => '<p>x</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Post::create([
            'author_id' => $author->id,
            'title' => 'Masih Draft',
            'slug' => 'masih-draft',
            'content' => '<p>x</p>',
            'status' => 'draft',
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk()->assertSee('Sudah Terbit')->assertDontSee('Masih Draft');
    }

    public function test_draft_post_detail_returns_404(): void
    {
        $author = User::factory()->create();
        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Draft',
            'slug' => 'draft-post',
            'content' => '<p>x</p>',
            'status' => 'draft',
        ]);

        $this->get(route('blog.show', $post))->assertNotFound();
    }

    public function test_admin_can_create_post_via_filament(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'admin');

        Livewire::test(CreatePost::class)
            ->fillForm([
                'author_id' => $admin->id,
                'title' => 'Judul Baru',
                'slug' => 'judul-baru',
                'content' => '<p>Konten baru</p>',
                'status' => 'draft',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('posts', [
            'slug' => 'judul-baru',
            'status' => 'draft',
        ]);
    }
}
