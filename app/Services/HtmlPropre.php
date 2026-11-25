<?php

namespace App\Services;

/**
 * Nettoie le HTML saisi dans l'ancien backoffice (souvent collé depuis Word) :
 * ne garde que la mise en forme simple, retire styles, scripts et attributs.
 */
class HtmlPropre
{
    private const BALISES = '<p><br><strong><b><em><i><u><ul><ol><li><a><h3><h4><blockquote>';

    public static function nettoyer(?string $html): string
    {
        $html = (string) $html;

        if ($html === '' || ! str_contains($html, '<')) {
            return nl2br(e(trim($html)));
        }

        $html = preg_replace('#<(script|style|xml|head|iframe|object)[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<!--.*?-->#s', '', $html);
        $html = preg_replace('#<(/?)h[12]\b#i', '<$1h3', $html);
        $html = strip_tags($html, self::BALISES);

        // Supprime tous les attributs sauf href (http/https/mailto/tel) sur les liens.
        $html = preg_replace_callback('#<(\w+)([^>]*)>#', function ($m) {
            $tag = strtolower($m[1]);
            if ($tag === 'a' && preg_match('#href\s*=\s*["\']((?:https?:|mailto:|tel:)[^"\']*)["\']#i', $m[2], $h)) {
                return '<a href="'.e($h[1]).'" target="_blank" rel="noopener nofollow">';
            }

            return '<'.$tag.'>';
        }, $html);

        $html = preg_replace('#(<br>\s*){3,}#i', '<br><br>', $html);
        $html = preg_replace('#<p>(\s|&nbsp;|<br>|<(strong|b|em|i|u)>\s*<br>)*#i', '<p>$2', $html);
        $html = preg_replace('#<p><(strong|b|em|i|u)>(?!\w)#i', '<p><$1>', $html);
        $html = preg_replace('#<p>\s*(&nbsp;|<br>)?\s*</p>#i', '', $html);

        return trim($html);
    }

    public static function texte(?string $html, int $limite = 160): string
    {
        $texte = html_entity_decode(strip_tags(preg_replace('#<(style|script|xml)[^>]*>.*?</\1>#is', '', (string) $html)), ENT_QUOTES, 'UTF-8');

        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', $texte)), $limite);
    }
}
