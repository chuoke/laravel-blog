<?php

namespace Chuoke\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Chuoke\Blog\Actions\UidGenerate;
use Chuoke\Blog\Models\Category;
use Chuoke\Blog\Models\Tag;
use Chuoke\Blog\Models\Post;

class BlogDatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Categories
        $categories = [
            ['en' => 'Technology', 'zh' => '技术'],
            ['en' => 'Lifestyle', 'zh' => '生活方式'],
            ['en' => 'Programming', 'zh' => '编程'],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $category = Category::create([
                'name' => $cat,
                'slug' => Str::slug($cat['en']),
                'description' => ['en' => "Articles about {$cat['en']}", 'zh' => "关于 {$cat['zh']} 的文章"],
                'sort_order' => 0,
            ]);
            $categoryIds[] = $category->id;
        }

        // 2. Create Tags
        $tags = [
            ['en' => 'Laravel', 'zh' => 'Laravel'],
            ['en' => 'Vue.js', 'zh' => 'Vue.js'],
            ['en' => 'Career', 'zh' => '职业发展'],
            ['en' => 'Tutorial', 'zh' => '教程'],
        ];

        $tagIds = [];
        foreach ($tags as $t) {
            $tag = Tag::create([
                'name' => $t,
                'slug' => Str::slug($t['en']),
            ]);
            $tagIds[] = $tag->id;
        }

        // 3. Create Posts
        // Assumes a user with ID 1 exists in the host app's author table.
        $authorId = 1;

        $posts = [
            [
                'title' => 'Getting Started with Laravel 11',
                'slug' => 'getting-started-with-laravel-11',
                'language' => 'en',
                'summary' => 'A comprehensive guide to the new features in Laravel 11.',
                'content' => '# Laravel 11\n\nLaravel 11 introduces a streamlined application structure...',
                'status' => 'published',
                'category_id' => $categoryIds[2], // Programming
                'is_pinned' => true,
            ],
            [
                'title' => 'Laravel 11 入门指南', // Chinese translation of the post above
                'slug' => 'getting-started-with-laravel-11-zh',
                'language' => 'zh',
                'summary' => 'Laravel 11 新特性的全面指南。',
                'content' => '# Laravel 11\n\nLaravel 11 引入了精简的应用程序结构...',
                'status' => 'published',
                'category_id' => $categoryIds[2], // Programming
                'is_pinned' => true,
            ],
            [
                'title' => 'The Future of Web Development',
                'slug' => 'the-future-of-web-development',
                'language' => 'en',
                'summary' => 'Exploring upcoming trends in web technologies.',
                'content' => '## Web Trends\n\nWhat is the future holding for us?',
                'status' => 'published',
                'category_id' => $categoryIds[0], // Tech
                'is_pinned' => false,
            ]
        ];

        $articleGroupId = null;

        foreach ($posts as $index => $postData) {
            $postData['uid'] = app(UidGenerate::class)->execute();
            $postData['author_id'] = $authorId;
            $postData['published_at'] = now()->subDays(rand(1, 10));
            $postData['view_count'] = rand(10, 500);

            // The first two posts belong to the same article group (original + zh translation).
            $postData['article_id'] = $index === 1 ? $articleGroupId : null;

            $post = Post::create($postData);

            // If this is the original (article_id is null), self-assign it and
            // remember it so the next post in the group can reference it.
            if ($post->article_id === null) {
                $post->update(['article_id' => $post->id]);
            }
            if ($index === 0) {
                $articleGroupId = $post->article_id;
            }

            // Attach tags once per article group: the pivot is keyed by article_id,
            // so tags are shared across all translations of the same article.
            if ($index === 1) {
                continue;
            }

            if ($index === 0) {
                $post->tags()->attach([$tagIds[0], $tagIds[3]]); // Laravel, Tutorial
            } else {
                $post->tags()->attach([$tagIds[1]]); // Vue.js
            }
        }
    }
}
