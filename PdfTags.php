<?php

namespace dokuwiki\plugin\bootswrapper;

/** PDF presentation for the Tag plugin, without modifying its renderer. */
class PdfTags
{
    public static function render(string $html, string $label): string
    {
        // Transform only the Tag plugin's lists, preserving the rest of the document.
        return preg_replace_callback(
            '~<div\b[^>]*\bclass=(["\'])(?:tags|tagstop)\1[^>]*>.*?</div>~si',
            static function ($match) use ($label) {
                $dom = new \DOMDocument('1.0', 'UTF-8');
                $previous = libxml_use_internal_errors(true);
                $loaded = $dom->loadHTML('<?xml encoding="utf-8"?>' . $match[0]);
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
                if (!$loaded) return $match[0];

                $badges = [];
                foreach ($dom->getElementsByTagName('a') as $anchor) {
                    if (!in_array('tag', preg_split('/\s+/', trim($anchor->getAttribute('rel'))), true)) continue;
                    if (!$anchor->hasAttribute('href')) continue;
                    $text = trim($anchor->textContent);
                    if ($text === '') continue;

                    // Fixed-pitch SVG text gives predictable padding without font measurement
                    // services. It remains vector text in the PDF, not a raster screenshot.
                    $width = (int) ceil(28 + 7.2 * mb_strwidth($text, 'UTF-8'));
                    $right = $width - 1;
                    $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width
                        . '" height="22" viewBox="0 0 ' . $width . ' 22">'
                        . '<path d="M1 11 L11 1 H' . $right . ' V21 H11 Z" fill="#eef2f5" stroke="#aebdca"/>'
                        . '<circle cx="8" cy="11" r="1.5" fill="#8193a3"/>'
                        . '<text x="16" y="15" font-family="DejaVu Sans Mono" font-size="12" fill="#334155">'
                        . $escaped . '</text></svg>';

                    // Preserve the original URL, including query parameters and fragments.
                    $badges[] = '<a href="' . hsc($anchor->getAttribute('href')) . '">'
                        . '<img src="data:image/svg+xml;base64,' . base64_encode($svg)
                        . '" width="' . $width . '" height="22" alt="' . hsc($text) . '" /></a>';
                }
                if (!$badges) return $match[0];
                return '<div class="bs-pdf-tags"><strong>' . hsc($label) . '</strong> '
                    . implode(' ', $badges) . '</div>';
            },
            $html
        );
    }
}
