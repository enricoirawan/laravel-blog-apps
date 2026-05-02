<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class Avatarupload extends Component
{
    use WithFileUploads;

    public $avatar;

    public function save()
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $this->validate([
            'avatar' => 'required|image|max:1000'
        ]);

        $user = auth()->user();
        $filename = $user->id . "-" . \uniqid() . ".jpg";

        Image::make($this->avatar)
            ->fit(120, 120)
            ->save(storage_path('app/public/avatars/' . $filename));

        $oldAvatar = $user->avatar;
        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg") {
            Storage::disk('public')->delete(str_replace("/storage/", "", $oldAvatar));
        }

        session()->flash('success', 'Congratz on the new avatar');
        return $this->redirect('/manage-avatar', navigate: true);
    }

    public function render()
    {
        return view('livewire.avatarupload');
    }
}
