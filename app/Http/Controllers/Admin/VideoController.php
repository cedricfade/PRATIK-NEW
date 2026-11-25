<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        return view('admin.news.videos', ['videos' => VideoInfo::orderByDesc('id')->paginate(24)]);
    }

    public function store(Request $request): RedirectResponse
    {
        VideoInfo::create($this->valider($request));

        return back()->with('success', 'Vidéo ajoutée.');
    }

    public function update(Request $request, VideoInfo $video): RedirectResponse
    {
        $video->update($this->valider($request));

        return back()->with('success', 'Vidéo modifiée.');
    }

    public function destroy(VideoInfo $video): RedirectResponse
    {
        $video->delete();

        return back()->with('success', 'Vidéo supprimée.');
    }

    private function valider(Request $request): array
    {
        $data = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'lien' => ['required', 'url', 'max:2000', 'regex:~(youtube\.com|youtu\.be|youtube-nocookie\.com)~i'],
        ], ['lien.regex' => 'Seuls les liens YouTube sont acceptés.']);

        // On stocke toujours la forme "embed", comme l'ancien site.
        if (preg_match('~(?:embed/|v=|youtu\.be/|shorts/)([A-Za-z0-9_-]{11})~', $data['lien'], $m)) {
            $data['lien'] = 'https://www.youtube.com/embed/'.$m[1];
        }

        return $data;
    }
}
