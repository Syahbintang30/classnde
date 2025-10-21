<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $sitemap .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        
        // Nde Official Page
        $sitemap .= '    <url>' . "\n";
        $sitemap .= '        <loc>' . url('/ndeofficial') . '</loc>' . "\n";
        $sitemap .= '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= '        <changefreq>weekly</changefreq>' . "\n";
        $sitemap .= '        <priority>1.0</priority>' . "\n";
        $sitemap .= '        <image:image>' . "\n";
        $sitemap .= '            <image:loc>' . asset('compro/img/ndehero.JPEG') . '</image:loc>' . "\n";
        $sitemap .= '            <image:title>Nde Official - Guitar Sessions &amp; Brand Ambassador</image:title>' . "\n";
        $sitemap .= '            <image:caption>Alfarezi (Nde) playing guitar, Brand Ambassador for Crafter and Enya guitars</image:caption>' . "\n";
        $sitemap .= '        </image:image>' . "\n";
        $sitemap .= '        <image:image>' . "\n";
        $sitemap .= '            <image:loc>' . asset('compro/img/ndelogo.png') . '</image:loc>' . "\n";
        $sitemap .= '            <image:title>Nde Official Logo</image:title>' . "\n";
        $sitemap .= '            <image:caption>Official logo of Nde - Guitar instructor and content creator</image:caption>' . "\n";
        $sitemap .= '        </image:image>' . "\n";
        $sitemap .= '    </url>' . "\n";
        
        // Register Class Page
        $sitemap .= '    <url>' . "\n";
        $sitemap .= '        <loc>' . route('registerclass') . '</loc>' . "\n";
        $sitemap .= '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= '        <changefreq>weekly</changefreq>' . "\n";
        $sitemap .= '        <priority>0.8</priority>' . "\n";
        $sitemap .= '    </url>' . "\n";
        
        // Login Page
        $sitemap .= '    <url>' . "\n";
        $sitemap .= '        <loc>' . route('login') . '</loc>' . "\n";
        $sitemap .= '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= '        <changefreq>monthly</changefreq>' . "\n";
        $sitemap .= '        <priority>0.6</priority>' . "\n";
        $sitemap .= '    </url>' . "\n";
        
        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }
}
