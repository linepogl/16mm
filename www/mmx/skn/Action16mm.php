<?php

class Action16mm extends MMAction {

	private $q;
	private $what;
	private $page;
	public function __construct($q = null,$what=null,$page=null){ parent::__construct(); $this->q = $q; $this->what = $what; $this->page = $page; }
	public function GetUrlArgs(){ return ['q'=>$this->q,'what'=>$this->what,'page'=>$this->page] + parent::GetUrlArgs(); }
	public static function Make(){ return new static( Http::$GET['q']->AsStringOrNull() , Http::$GET['what']->AsStringOrNull() , Http::$GET['page']->AsIntegerOrNull() ); }

	public function RenderJavascript(){
		$q = trim($this->q);

		if (empty($q)) {
			echo "window.mmx.ShowSearch();";
		}
		elseif ($this->what === 'actors') {
			$page = max(1,intval($this->page));
			$found = false;
			echo "window.mmx.AddSeparator('People ($page)');";
			/** @var $actor Actor */
			foreach (Actor::Search($q,$page) as $actor) {
				echo "window.mmx.AddActorTile(".$actor->ToJson().");";
				$found = true;
			}
			if ($found) {
				$act = new self($this->q, $this->what, $page + 1);
				$a = '<a class="mmx-more" href="'.new Html($act).'" onclick="window.mmx.OpenTab(null,'.new Html(new Js($act)).');return false;">More '.oxy::icoRight().'</a>';
				echo "window.mmx.Add(".new Js($a).");";
			}
			echo "window.mmx.ResolveSeparator('No person found.',".new Js(oxy::icoEmpty()).");";
		}
		elseif ($this->what === 'movies') {
			$page = max(1,intval($this->page));
			$found = false;
			echo "window.mmx.AddSeparator('Productions ($page)');";
			/** @var $movie Movie */
			foreach (Movie::Search($this->q,$page) as $movie) {
				echo "window.mmx.AddMovieTile(".$movie->ToJson().");";
				$found = true;
			}
			/** @var $chain Chain */
			foreach (Chain::Search($this->q,$page) as $chain) {
				echo "window.mmx.AddMovieTile(".$chain->ToJson().");";
				$found = true;
			}
			if ($found) {
				$act = new self($this->q, $this->what, $page + 1);
				$a = '<a class="mmx-more" href="'.new Html($act).'" onclick="window.mmx.OpenTab(null,'.new Html(new Js($act)).');return false;">More '.oxy::icoRight().'</a>';
				echo "window.mmx.Add(".new Js($a).");";
			}
			echo "window.mmx.ResolveSeparator('No person found.',".new Js(oxy::icoEmpty()).");";

		}
		else {
			echo "window.mmx.AddSeparator('People');";
			$i = 5;
			$found = false;
			/** @var $actor Actor */
			foreach (Actor::Search($q) as $actor) {
				echo "window.mmx.AddActorTile(".$actor->ToJson().");";
				$found = true;
				if (--$i<=0) break;
			}
			if ($found) {
				$act = new self($this->q, 'actors', 1);
				$a = '<a class="mmx-more" href="'.new Html($act).'" onclick="window.mmx.OpenTab(null,'.new Html(new Js($act)).');return false;">More '.oxy::icoRight().'</a>';
				echo "window.mmx.Add(".new Js($a).");";
			}
			echo "window.mmx.ResolveSeparator('No person found.',".new Js(oxy::icoEmpty()).");";

			echo "window.mmx.AddSeparator('Productions');";
			$a = Movie::Search($q);
			$b = Chain::Search($q);
			$i = min(5,count($a));
			$j = min(5,count($b));
			$ii = 5-$j;
			$jj = 5-$i;
			$i += $ii;
			$j += $jj;
			$found = false;
			/** @var $movie Movie */
			foreach ($a as $movie) {
				echo "window.mmx.AddMovieTile(".$movie->ToJson().");";
				$found = true;
				if (--$i<=0) break;
			}
			/** @var $chain Chain */
			foreach ($b as $chain) {
				echo "window.mmx.AddMovieTile(".$chain->ToJson().");";
				$found = true;
				if (--$j<=0) break;
			}
			if ($found) {
				$act = new self($this->q, 'movies', 1);
				$a = '<a class="mmx-more" href="'.new Html($act).'" onclick="window.mmx.OpenTab(null,'.new Html(new Js($act)).');return false;">More '.oxy::icoRight().'</a>';
				echo "window.mmx.Add(".new Js($a).");";
			}
			echo "window.mmx.ResolveSeparator('No production found.',".new Js(oxy::icoEmpty()).");";
		}
	}
}