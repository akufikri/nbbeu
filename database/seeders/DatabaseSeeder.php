<?php

namespace Database\Seeders;

use App\Models\OrgChart;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'member']);

        $admin1 = User::factory()->create([
            'name' => 'Admin One',
            'email' => 'admin1@nbbeu.test',
        ]);
        $admin1->assignRole('admin');

        $admin2 = User::factory()->create([
            'name' => 'Admin Two',
            'email' => 'admin2@nbbeu.test',
        ]);
        $admin2->assignRole('admin');

        $this->seedDummyOrgChart();
        $this->seedDummyPosts($admin1);
    }

    /**
     * Placeholder leadership entries so the landing page's Leadership
     * section isn't empty during development — replace with real data
     * via the admin panel once available.
     */
    private function seedDummyOrgChart(): void
    {
        $members = [
            ['name' => 'Ahmad Zulkifli', 'position' => 'President', 'photo' => 'org-chart/leader-1.webp'],
            ['name' => 'Sarah Lim', 'position' => 'Vice President', 'photo' => 'org-chart/leader-2.webp'],
            ['name' => 'David Wong', 'position' => 'Secretary', 'photo' => 'org-chart/leader-3.webp'],
            ['name' => 'Nurul Aisyah', 'position' => 'Treasurer', 'photo' => 'org-chart/leader-4.webp'],
        ];

        foreach ($members as $index => $member) {
            OrgChart::create([
                ...$member,
                'display_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Placeholder blog posts so the landing page's Blog section isn't
     * empty during development — replace with real posts via the admin panel.
     */
    private function seedDummyPosts(User $author): void
    {
        $posts = [
            [
                'title' => 'NBBEU Officially Launches to Unite Banking Executives',
                'cover_image' => 'posts/post-1.png',
                'category' => 'symposium',
                'excerpt' => 'North Borneo Banking Executive Union opens its doors to banking leaders across the region, marking a new chapter in industry collaboration.',
                'content' => '<p>North Borneo Banking Executive Union (NBBEU) has officially launched, bringing together banking executives from across North Borneo under one professional community.</p><p>The union aims to foster collaboration, share industry insight, and advocate for stronger banking standards throughout the region.</p>',
            ],
            [
                'title' => 'Why Professional Networks Matter in Banking',
                'cover_image' => 'posts/post-2.png',
                'category' => 'reports',
                'excerpt' => 'A strong professional network helps banking executives navigate industry changes, share best practices, and build lasting partnerships.',
                'content' => '<p>In an industry as fast-moving as banking, having a trusted network of peers can make all the difference.</p><p>NBBEU members gain access to exclusive events, knowledge sharing sessions, and a community built on mutual growth.</p>',
            ],
            [
                'title' => 'Member Certification: What It Means for Your Career',
                'cover_image' => 'posts/post-3.png',
                'category' => 'certification',
                'excerpt' => 'An official NBBEU member card and certificate is more than recognition — it is a mark of credibility within the banking profession.',
                'content' => '<p>Upon approval, every NBBEU member receives an official member card and certificate, verifiable through a public QR code.</p><p>This recognition signals your standing as a trusted banking professional in North Borneo.</p>',
            ],
        ];

        foreach ($posts as $index => $post) {
            Post::create([
                'author_id' => $author->id,
                'title' => $post['title'],
                'slug' => \Illuminate\Support\Str::slug($post['title']),
                'excerpt' => $post['excerpt'],
                'category' => $post['category'],
                'content' => $post['content'],
                'cover_image' => $post['cover_image'],
                'hero_image' => $post['cover_image'],
                'status' => 'published',
                'published_at' => now()->subDays($index * 3),
            ]);
        }
    }
}
