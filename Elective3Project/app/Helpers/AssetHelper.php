<?php

namespace App\Helpers;

class AssetHelper
{
    public static function assetFromManifest($source)
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            return asset($source);
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        if (!isset($manifest[$source])) {
            return asset($source);
        }
        
        return asset('build/' . $manifest[$source]['file']);
    }
}
