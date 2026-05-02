<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;

class Createpost extends Component
{
    public $title;
    public $body;

    public function create()
    {
        if (!auth()->check()) {
            abort(403, 'Unathorized');
        }

        $incomingFields = $this->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $incomingFields['title'] = \strip_tags($incomingFields['title']);
        $incomingFields['body'] = \strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost =  Post::create($incomingFields);
        session()->flash('success', 'New post successfully created');
        return $this->redirect("/post/{$newPost->id}", navigate: true);
    }

    public function render()
    {
        return view('livewire.createpost');
    }
}
