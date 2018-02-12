<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\models\TertArticle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Sunra\PhpSimple\HtmlDomParser;
use App\Http\Requests\Item\Create;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;


use Intervention\Image\Facades\Image;


class TertArticlesCRUDController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

         $articles = TertArticle::orderBy('id', 'DESC')->paginate(5);
        return view('TertArticlesCRUD.index', compact('articles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     *Get grab the data of the last 1000 articles (title, description, main image, date, url)
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        $url = "http://www.tert.am/am/news/search?q=հոդված";

        $dom = HtmlDomParser::file_get_html($url);

        $count_need_articles = 1000;

        $count_grabbed_articles = count($dom->find('.news-blocks'));

        $loop_end_num = ceil($count_need_articles / $count_grabbed_articles);

        echo "<pre>";

        date_default_timezone_set('Asia/Yerevan');

        /*Delete tertarticles all data*/
        DB::table('tertarticles')->delete();

        for ($i = 0; $i < $loop_end_num; $i++) {

            foreach ($dom->find('.news-blocks') as $e) {

                $img_url = $e->children[0]->children[0]->attr['src'];

                $article_url = $e->children[0]->attr['href'];

                $article_date_time = $e->children[1]->children[0]->innertext();

                $article_category = $e->children[1]->children[2]->innertext();

                $article_title = $e->children[2]->children[0]->innertext();

                $article_description = $e->children[3]->children[0]->innertext();


                $article_date_exploded = explode(" ", $article_date_time);

                $article_date = $article_date_exploded[2];

                $article_time = $article_date_exploded[0];

                $article_date_time_full = $article_date . " " . $article_time;

//                $img_url_exploded = explode(".", $img_url);
//
//                $img_extension = $img_url_exploded[3];


                $imgFile = Image::make($img_url);

                $path = $img_url;

                $qpos = strpos($path, "?");

                if ($qpos !== false) $path = substr($path, 0, $qpos);

                $extensionImage = pathinfo($path, PATHINFO_EXTENSION);

                $imgFileName = str_random(16) . "." . $extensionImage;

                $imgFile->save('images/tert.am/' . $imgFileName);

                //$extensionImage = $imgFile->getClientOriginalExtension();

                //Storage::move( asset("uploads/".$imageName),asset("images/tert.am/".$imgFileName));

                /* inserting current article */
                $article_curr = new TertArticle();

                $article_curr->title = $article_title;

                $article_curr->description = $article_description;

                $article_curr->img_url = $img_url;

                $article_curr->img_local_url = $imgFileName;

                $article_curr->article_url = $article_url;

                $article_curr->date = $article_date_time_full;

                $article_curr->cat_name = $article_category;

                $article_curr->save();
            }
        }
        return redirect()->route('TertArticlesCRUD.index')
            ->with('success', 'Articles grabbed successfully');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('TertArticlesCRUD.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        TertArticle::create($request->all());

        return redirect()->route('TertArticlesCRUD.index')
            ->with('success', 'Article created successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $article = TertArticle::find($id);
        return view('TertArticlesCRUD.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $article = TertArticle::find($id);
        return view('TertArticlesCRUD.edit', compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd(1);
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',

        ]);
        TertArticle::find($id)->update($request->all());
        return redirect()->route('TertArticlesCRUD.index')
            ->with('success', 'Article updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        TertArticle::find($id)->delete();
        return redirect()->route('TertArticlesCRUD.index')
            ->with('success', 'Article deleted successfully');
    }

    public function destroyAll()
    {
        DB::table('tertarticles')->delete();

        $recycler = new Filesystem();

        $recycler->cleanDirectory('images/tert.am/');


        return redirect()->route('TertArticlesCRUD.index')
            ->with('success', 'All articles deleted successfully');
    }

}