<?php

namespace Azuriom\Plugin\SkinApi\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Azuriom\Models\User;

class TextureJsonController extends Controller
{
    public function handle(string $username)
    {
        $user = User::where('name', $username)->first();

        $result = [];
        $userId = $user ? $user->id : null;

        //SKIN
        if ($userId && Storage::disk('public')->exists("skins/{$userId}.png")) {
            $skinFile = "skins/{$userId}.png";
        } elseif (Storage::disk('public')->exists("skins/default.png")) {
            $skinFile = "skins/default.png";
        } else {
            $skinFile = null;
        }

        if ($skinFile) {
            $skinPath = Storage::disk('public')->path($skinFile);

            $result['SKIN'] = [
                'url' => url("/api/skin-api/skins/$username"),
                'digest' => hash_file('sha256', $skinPath),
                'metadata' => [
                    'model' => $this->detectModel($skinPath)
                ]
            ];
        }

        //CAPE
        if ($userId && Storage::disk('public')->exists("capes/{$userId}.png")) {
            $capeFile = "capes/{$userId}.png";
        } elseif (Storage::disk('public')->exists("capes/default.png")) {
            $capeFile = "capes/default.png";
        } else {
            $capeFile = null;
        }

        if ($capeFile) {
            $capePath = Storage::disk('public')->path($capeFile);

            $result['CAPE'] = [
                'url' => url("/api/skin-api/capes/$username"),
                'digest' => hash_file('sha256', $capePath)
            ];
        }

        return response()->json($result, 200, [], JSON_UNESCAPED_SLASHES);
    }

    private function detectModel(string $path): string
    {
      $img = imagecreatefrompng($path);

      $rgb = imagecolorat($img,55,20);
      $colors = imagecolorsforindex($img, $rgb);
      $alpha = $colors["alpha"];
      imagedestroy($img);


      return $alpha === 127 ? 'slim' : 'default';
    }
}
