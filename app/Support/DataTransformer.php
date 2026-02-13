<?php

namespace App\Support;

class DataTransformer
{
    /**
     * Transform homepage API data into template variables.
     */
    public function transformHomepage(array $data): array
    {
        return array_merge($this->baseProps($data), [
            'heading' => $data['heroTitle'] ?? 'Welcome',
            'headerImage' => $data['heroImage'] ?? null,
            'headerVideo' => $data['heroVideo'] ?? null,
            'actionLinks' => $data['actionLinks'] ?? [],
            'showEvents' => $data['sections']['showEvents'] ?? false,
            'events' => $data['events'] ?? [],
            'showSermons' => $data['sections']['showSermons'] ?? false,
            'series' => $data['sermons']['activeSeries'] ?? [],
            'showMinistries' => $data['sections']['showMinistries'] ?? false,
            'ministries' => $data['ministries'] ?? [],
        ]);
    }

    /**
     * Resolve the template name and transform page data.
     *
     * @return array{0: string, 1: array}
     */
    public function transformPage(string $template, string $path, array $data): array
    {
        return match ($template) {
            'about' => ['about', $this->transformAbout($data)],
            'leadership' => ['leadership', $this->transformLeadership($data)],
            'content-page' => $this->resolveContentPage($path, $data),
            'ministry-detail' => ['ministry', $this->transformMinistry($data)],
            'blog-post' => ['blog-post', $this->transformBlogPost($data)],
            'contact' => ['location', $this->transformContact($data)],
            default => ['custom-page', $this->transformCustomPage($data)],
        };
    }

    /**
     * Build base props common to all pages (navbar, footer, nav booleans).
     */
    protected function baseProps(array $data): array
    {
        $footer = $data['footer'] ?? [];
        $navItems = $data['navItems'] ?? [];

        return [
            // Site info
            'siteName' => $data['siteName'] ?? 'Your Church',
            'siteTitle' => $data['siteTitle'] ?? $data['siteName'] ?? 'Your Church',
            'navLogo' => $data['navLogo'] ?? null,

            // Footer flattened
            'footerAbout' => $footer['about'] ?? '',
            'churchAddress' => $footer['contact']['address'] ?? null,
            'mailingAddress' => $footer['contact']['mailingAddress'] ?? null,
            'churchPhone' => $footer['contact']['phone'] ?? null,
            'churchEmail' => $footer['contact']['email'] ?? null,
            'mapUrl' => $data['mapUrl'] ?? $footer['contact']['mapUrl'] ?? null,
            'scheduleSections' => $footer['serviceSchedule']['sections'] ?? $footer['serviceSchedule'] ?? [],
            'facebookUrl' => $footer['social']['facebook'] ?? null,
            'youtubeUrl' => $footer['social']['youtube'] ?? null,
            'instagramUrl' => $footer['social']['instagram'] ?? null,

            // Nav booleans derived from navItems
            ...$this->navBooleans($navItems),
        ];
    }

    /**
     * Parse navItems to derive boolean props for the navbar component.
     */
    protected function navBooleans(array $navItems): array
    {
        $showMinistries = false;
        $showEvents = false;
        $showLeadership = false;
        $showGospel = false;
        $showDoctrine = false;
        $showConstitution = false;
        $showSermons = false;
        $hasBlogPosts = false;
        $aboutUsPages = [];
        $resourcesPages = [];

        $standardAboutLabels = ['About Us', 'Leadership', 'The Gospel', 'What We Believe', 'Constitution'];
        $standardResourceLabels = ['Sermons', 'Blog'];

        foreach ($navItems as $item) {
            $label = $item['label'] ?? '';

            if ($label === 'Ministries') {
                $showMinistries = true;
            }

            if ($label === 'Upcoming Events') {
                $showEvents = true;
            }

            $children = $item['children'] ?? [];

            if ($label === 'About Us') {
                foreach ($children as $child) {
                    $childLabel = $child['label'] ?? '';
                    match ($childLabel) {
                        'Leadership' => $showLeadership = true,
                        'The Gospel' => $showGospel = true,
                        'What We Believe' => $showDoctrine = true,
                        'Constitution' => $showConstitution = true,
                        default => null,
                    };

                    if (! in_array($childLabel, $standardAboutLabels)) {
                        $aboutUsPages[] = $child;
                    }
                }
            }

            if ($label === 'Resources') {
                foreach ($children as $child) {
                    $childLabel = $child['label'] ?? '';
                    match ($childLabel) {
                        'Sermons' => $showSermons = true,
                        'Blog' => $hasBlogPosts = true,
                        default => null,
                    };

                    if (! in_array($childLabel, $standardResourceLabels)) {
                        $resourcesPages[] = $child;
                    }
                }
            }
        }

        return [
            'showMinistries' => $showMinistries,
            'showEvents' => $showEvents,
            'showLeadership' => $showLeadership,
            'showGospel' => $showGospel,
            'showDoctrine' => $showDoctrine,
            'showConstitution' => $showConstitution,
            'showSermons' => $showSermons,
            'hasBlogPosts' => $hasBlogPosts,
            'aboutUsPages' => $aboutUsPages,
            'resourcesPages' => $resourcesPages,
        ];
    }

    protected function transformAbout(array $data): array
    {
        $sections = collect($data['sections'] ?? [])->map(function ($section) {
            return array_merge($section, [
                'title' => $section['heading'] ?? $section['title'] ?? '',
            ]);
        })->toArray();

        return array_merge($this->baseProps($data), [
            'siteTitle' => $data['siteTitle'] ?? 'About',
            'aboutHeading' => $data['pageTitle'] ?? 'About Us',
            'aboutSubheading' => $data['pageSubtitle'] ?? '',
            'aboutImage' => $data['headerImage'] ?? null,
            'aboutSections' => $sections,
        ]);
    }

    protected function transformLeadership(array $data): array
    {
        return array_merge($this->baseProps($data), [
            'siteTitle' => $data['siteTitle'] ?? 'Leadership',
            'seniorLeaders' => $data['seniorLeaders'] ?? [],
            'leadershipSections' => $data['leadershipSections'] ?? [],
            'lookingForPastor' => $data['lookingForPastor'] ?? false,
            'lookingForPastorMessage' => $data['lookingForPastorMessage'] ?? null,
        ]);
    }

    /**
     * Resolve content-page template to the specific theme view based on URL path.
     *
     * @return array{0: string, 1: array}
     */
    protected function resolveContentPage(string $path, array $data): array
    {
        $base = $this->baseProps($data);
        $title = $data['pageTitle'] ?? '';
        $content = $data['pageContent'] ?? '';
        $siteTitle = $data['siteTitle'] ?? $title;

        if (str_starts_with($path, 'gospel')) {
            return ['gospel', array_merge($base, [
                'siteTitle' => $siteTitle,
                'gospelHeading' => $title,
                'gospelContent' => $content,
            ])];
        }

        if (str_starts_with($path, 'doctrine') || str_starts_with($path, 'beliefs') || str_starts_with($path, 'statement-of-faith')) {
            return ['doctrine', array_merge($base, [
                'siteTitle' => $siteTitle,
                'doctrineHeading' => $title,
                'doctrineContent' => $content,
            ])];
        }

        if (str_starts_with($path, 'constitution') || str_starts_with($path, 'bylaws')) {
            return ['constitution', array_merge($base, [
                'siteTitle' => $siteTitle,
                'constitutionHeading' => $title,
                'constitutionContent' => $content,
            ])];
        }

        // Fallback: render as custom page
        return ['custom-page', array_merge($base, [
            'siteTitle' => $siteTitle,
            'customPageTitle' => $title,
            'customPageContent' => $content,
        ])];
    }

    protected function transformMinistry(array $data): array
    {
        return array_merge($this->baseProps($data), [
            'siteTitle' => $data['siteTitle'] ?? 'Ministry',
            'ministryTitle' => $data['pageTitle'] ?? 'Ministry',
            'ministryImage' => $data['image'] ?? null,
            'ministryWhereMeets' => $data['whereMeets'] ?? null,
            'ministryContent' => $data['content'] ?? '',
        ]);
    }

    protected function transformBlogPost(array $data): array
    {
        return array_merge($this->baseProps($data), [
            'siteTitle' => $data['siteTitle'] ?? 'Blog Post',
            'blogPostTitle' => $data['pageTitle'] ?? 'Blog Post',
            'blogPostImage' => $data['image'] ?? null,
            'blogPostAuthorName' => $data['author'] ?? null,
            'blogPostContent' => $data['pageContent'] ?? '',
        ]);
    }

    protected function transformContact(array $data): array
    {
        return array_merge($this->baseProps($data), [
            'siteTitle' => $data['siteTitle'] ?? 'Contact',
        ]);
    }

    protected function transformCustomPage(array $data): array
    {
        return array_merge($this->baseProps($data), [
            'siteTitle' => $data['siteTitle'] ?? 'Page',
            'customPageTitle' => $data['pageTitle'] ?? 'Page',
            'customPageImage' => $data['featuredImage'] ?? $data['image'] ?? null,
            'customPageContent' => $data['pageContent'] ?? '',
        ]);
    }
}
