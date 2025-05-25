<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdminControlConfig;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    const CONTROLLER_NAME = "Setting Controller";

    # Setting List and Save Function 
    public function index(Request $request)
    {
        try {
            if ($request->keys) {
                $settings = Setting::whereIn('key', $request->keys)->pluck('value', 'key') ?? [];
            } else {
                $settings = Setting::pluck('value', 'key') ?? [];
            }
            return $this->actionSuccess('Setting list get successfully',  $settings);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
    }



    

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $is_delete = $request->boolean('is_delete');
            $settings = $request->except(['image', 'is_delete', '_method', 'pwa_logo_192', 'pwa_logo_512']);
            $userId = Auth::user()->uuid;

            foreach ($settings as $key => $value) {
                $existingSetting = Setting::where('key', $key)->first();

                if ($existingSetting) {
                    $existingSetting->value = $value;
                    $existingSetting->updated_by = $userId;
                    $existingSetting->save();
                } else {
                    Setting::create([
                        'key' => $key,
                        'value' => $value,
                        'created_by' => $userId,
                    ]);
                }
            }

            // Delete logos if requested
            if ($is_delete) {
                $logos = ['company_logo', 'pwa_logo_192', 'pwa_logo_512'];
                foreach ($logos as $logoKey) {
                    $existLogo = Setting::where('key', $logoKey)->first();

                    if ($existLogo && $existLogo->value) {
                        $relativePath = Str::after($existLogo->value, '/storage/');
                        if (Storage::disk('public')->exists($relativePath)) {
                            Storage::disk('public')->delete($relativePath);
                        }
                        $existLogo->value = null;
                        $existLogo->updated_by = $userId;
                        $existLogo->save();
                    }
                }
            }

            // Handle file upload for company logo
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $directory = "companyLogs";
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory, 0755, true);
                }

                $path = $request->file('image')->store($directory, 'public');
                $image_url = url('storage/' . $path);

                $logoKey = 'company_logo';
                $existingLogo = Setting::where('key', $logoKey)->first();

                if ($existingLogo) {
                    $existingLogo->value = $image_url;
                    $existingLogo->updated_by = $userId;
                    $existingLogo->save();
                } else {
                    Setting::create([
                        'key' => $logoKey,
                        'value' => $image_url,
                        'created_by' => $userId,
                    ]);
                }
            }

            // Handle file upload for PWA logo 192x192
            if ($request->hasFile('pwa_logo_192') && $request->file('pwa_logo_192')->isValid()) {
                $file = $request->file('pwa_logo_192');
                // Validate file type
                if ($file->getMimeType() !== 'image/png') {
                    throw new \Exception('PWA logo (192x192) must be a PNG image.');
                }
                // Validate dimensions
                $imageInfo = getimagesize($file->getRealPath());
                if (!$imageInfo || $imageInfo[0] !== 192 || $imageInfo[1] !== 192) {
                    throw new \Exception('PWA logo (192x192) must be 192x192 pixels.');
                }

                $directory = "pwaLogos";
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory, 0755, true);
                }

                $path = $file->store($directory, 'public');
                $image_url = url('storage/' . $path);

                $logoKey = 'pwa_logo_192';
                $existingLogo = Setting::where('key', $logoKey)->first();

                if ($existingLogo) {
                    $existingLogo->value = $image_url;
                    $existingLogo->updated_by = $userId;
                    $existingLogo->save();
                } else {
                    Setting::create([
                        'key' => $logoKey,
                        'value' => $image_url,
                        'created_by' => $userId,
                    ]);
                }
            }

            // Handle file upload for PWA logo 512x512
            if ($request->hasFile('pwa_logo_512') && $request->file('pwa_logo_512')->isValid()) {
                $file = $request->file('pwa_logo_512');
                // Validate file type
                if ($file->getMimeType() !== 'image/png') {
                    throw new \Exception('PWA logo (512x512) must be a PNG image.');
                }
                // Validate dimensions
                $imageInfo = getimagesize($file->getRealPath());
                if (!$imageInfo || $imageInfo[0] !== 512 || $imageInfo[1] !== 512) {
                    throw new \Exception('PWA logo (512x512) must be 512x512 pixels.');
                }

                $directory = "pwaLogos";
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory, 0755, true);
                }

                $path = $file->store($directory, 'public');
                $image_url = url('storage/' . $path);

                $logoKey = 'pwa_logo_512';
                $existingLogo = Setting::where('key', $logoKey)->first();

                if ($existingLogo) {
                    $existingLogo->value = $image_url;
                    $existingLogo->updated_by = $userId;
                    $existingLogo->save();
                } else {
                    Setting::create([
                        'key' => $logoKey,
                        'value' => $image_url,
                        'created_by' => $userId,
                    ]);
                }
            }

            DB::commit();
            return $this->actionSuccess('Settings updated successfully!', Setting::pluck('value', 'key'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->actionFailure($e->getMessage());
        }
    }
}
