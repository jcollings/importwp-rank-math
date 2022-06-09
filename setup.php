<?php

use ImportWP\Common\Addon\AddonBaseGroup;
use ImportWP\Common\Addon\AddonBasePanel;
use ImportWP\Common\Addon\AddonFieldDataApi;
use ImportWP\Common\Addon\AddonInterface;
use ImportWP\Common\Addon\AddonPanelDataApi;

iwp_register_importer_addon('Rank Math SEO', 'iwp-rank-math', function (AddonInterface $addon) {

    $addon->register_panel('Rank Math SEO', 'rank_math', function (AddonBasePanel $panel) {

        $panel->register_field('SEO Title', 'rank_math_title');

        $panel->register_field('SEO Description', 'rank_math_description');

        // Facebook
        $panel->register_group('Facebook', 'facebook', function (AddonBaseGroup $group) {

            $group->register_field('Facebook Title', 'rank_math_facebook_title');

            $group->register_field('Facebook Description', 'rank_math_facebook_description');

            $group->register_attachment_fields('Facebook Image', 'rank_math_facebook_image', 'Facebook Image Location')
                ->save('iwp_rank_math_save_meta_image');

            $group->register_field('Add icon overlay to Facebook thumbnail', 'rank_math_facebook_enable_image_overlay')
                ->options([
                    ['label' => 'On', 'value' => 'on'],
                    ['label' => 'Off', 'value' => 'off']
                ])
                ->default('off');
        });

        // Twitter
        $panel->register_group('Twitter', 'twitter', function (AddonBaseGroup $group) {

            $group->register_field('Use Data from Facebook Tab', 'rank_math_twitter_use_facebook')
                ->options([
                    ['label' => 'On', 'value' => 'on'],
                    ['label' => 'Off', 'value' => 'off']
                ])
                ->default('off');

            $group->register_field('Twitter Title', 'rank_math_twitter_title');

            $group->register_field('Twitter Description', 'rank_math_twitter_description');

            $group->register_field('Twitter Card Type', 'rank_math_twitter_card_type')
                ->options([
                    ['value' => 'summary_large_image', 'label' => 'Summary Card with Large Image'],
                    ['value' => 'summary_card', 'label' => 'Summary Card'],
                    ['value' => 'app', 'label' => 'App Card'],
                    ['value' => 'player', 'label' => 'Player Card'],
                ])
                ->default('summary_large_image');

            $group->register_attachment_fields('Twitter Image', 'rank_math_twitter_image', 'Twitter Image Location')
                ->save('iwp_rank_math_save_meta_image');

            $group->register_field('Add icon overlay to Twitter thumbnail', 'rank_math_twitter_image_overlay')
                ->options([
                    ['label' => 'On', 'value' => 'on'],
                    ['label' => 'Off', 'value' => 'off']
                ])
                ->default('off');
        });

        // Advanced

        $panel->register_field('Focus Keyword', 'rank_math_focus_keyword');

        $panel->register_field('Is Pillar Content', 'rank_math_pillar_content')
            ->options([
                    ['label' => 'On', 'value' => 'on'],
                    ['label' => 'Off', 'value' => 'off']
                ])
            ->default('off');

        // If index we dont store it, serialized: rank_math_robots
        $panel->register_field('Robots Meta Index', 'rank_math_robots_index')
            ->options([
                ['label' => 'Index', 'value' => 'index'],
                ['label' => 'No Index', 'value' => 'noindex'],
            ])
            ->default('index')
            ->save(false);

        // serialized: rank_math_robots = nofollow, noimageindex, nosnippet, noarchive
        $panel->register_field('Robots Meta Options', 'rank_math_robots_options')
            ->tooltip('Basic meta options: nofollow, noimageindex, nosnippet, noarchive')
            ->save(false);

        // rank_math_advanced_robots = a:3:{s:11:"max-snippet";s:2:"-1";s:17:"max-video-preview";s:2:"-1";s:17:"max-image-preview";s:5:"large";}
        $panel->register_field('Advanced Robots Meta Max Snippet', 'rank_math_advanced_robots_max_snippet')
            ->default(-1)
            ->save(false);

        $panel->register_field('Advanced Robots Meta Max Video Preview', 'rank_math_advanced_robots_max_video_preview')
            ->default(-1)
            ->save(false);

        $panel->register_field('Advanced Robots Meta Max Image Preview', 'rank_math_advanced_robots_max_image_preview')
            ->options([
                ['value' => 'large', 'label' => 'Large'],
                ['value' => 'standard', 'label' => 'Standard'],
                ['value' => 'none', 'label' => 'None'],
            ])
            ->default('large')
            ->save(false);

        $panel->register_field('Canonical URL', 'rank_math_canonical_url');

        $panel->save('iwp_rank_math_save_panel');
    });
});

/**
 * Save Rank Math image url and id fields
 * 
 * @param AddonFieldDataApi $api
 * @param string $key
 * @param array $conditions
 * 
 * @return void
 */
function iwp_rank_math_save_meta_image(AddonFieldDataApi $api)
{
    $image_id = $image_url = '';

    $attachment_ids = $api->process_attachment();
    if ($attachment_ids && !empty($attachment_ids)) {
        $image_id = array_shift($attachment_ids);
        $image_url = wp_get_attachment_url($image_id);
    }

    $field_id = $api->get_field_id();
    $api->update_meta($field_id, $image_url);
    $api->update_meta($field_id . '-id', $image_id);
}

/**
 * Save Rank Math rank_math_robots & rank_math_advanced_robots serialized fields.
 *
 * @param AddonPanelDataApi $api
 * 
 * @return void
 */
function iwp_rank_math_save_panel(AddonPanelDataApi $api)
{
    $meta = $api->get_meta();
    if (empty($meta)) {
        return;
    }

    $meta_data = array_reduce($meta, function ($carry, $item) {

        switch ($item['key']) {
            case "rank_math_robots_index":
                $carry['rank_math_robots'][] = $item['value'];
                break;
            case "rank_math_robots_options":

                $parts = array_map('trim', explode(',', $item['value']));
                $carry['rank_math_robots'] = array_merge($carry['rank_math_robots'], $parts);

                break;
            case "rank_math_advanced_robots_max_snippet":
                $carry['rank_math_advanced_robots']['max-snippet'] = $item['value'];
                break;
            case "rank_math_advanced_robots_max_video_preview":
                $carry['rank_math_advanced_robots']['max-video-preview'] = $item['value'];
                break;
            case "rank_math_advanced_robots_max_image_preview":
                $carry['rank_math_advanced_robots']['max-image-preview'] = $item['value'];
                break;
        }

        return $carry;
    }, ['rank_math_robots' => [], 'rank_math_advanced_robots' => []]);

    if (!empty($meta_data['rank_math_robots'])) {
        $api->update_meta('rank_math_robots', array_unique($meta_data['rank_math_robots']));
    }

    if (!empty($meta_data['rank_math_advanced_robots'])) {
        $api->update_meta('rank_math_advanced_robots', $meta_data['rank_math_advanced_robots']);
    }
}
