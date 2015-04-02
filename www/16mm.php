<?php
require('_.php');
Oxygen::Go();
$active_tab = Oxygen::GetActionName();
?>
<!DOCTYPE html><html><head>
<?= Oxygen::GetHead('16mm.css') ?>
<?= Js::GetLink("mmx/jsc/mousetrap.js"); ?>
<title>16mm</title>
<script type="text/javascript">
/*<![CDATA[*/
html = function(s) { return s===null || s===undefined ? '' : document.createTextNode(s).nodeValue; };
empty = function(s){ return s === '' || s === null || s === undefined || (typeof s === 'Array' && s.length === 0); };
join = function(a){ return (a === null || a === undefined) ? '' : a.join(', '); };
jQuery.fn.findAndSelf = function(selector) { return this.find(selector).add(this.filter(selector)) };
/*]]>*/
</script>
</head>
<body class="<?php echo Browser::GetCssClasses(); ?>">




<div id="mmx-toolbar">
	<?php $act=new Action16mm();   if ($act->IsPermitted()) { $tab=$act->GetName(); ?><a id="mmx-tab-<?=$tab?>" href="<?=new Html($act)?>" class="mmx-tab" onclick="window.mmx.OpenTab(<?=new Js($tab)?>,<?=new Html(new Js($act))?>);return false;"><?= mmx::icoSearchGlass(); ?></a><?php } ?>
	<?php $act=new ActionActors(); if ($act->IsPermitted()) { $tab=$act->GetName(); ?><a id="mmx-tab-<?=$tab?>" href="<?=new Html($act)?>" class="mmx-tab" onclick="window.mmx.OpenTab(<?=new Js($tab)?>,<?=new Html(new Js($act))?>);return false;"><?= mmx::icoActor();       ?></a><?php } ?>

<!--
	<div id="mmx-tab-actors" class="mmx-tab" onclick="window.mmx.ShowTab('actors')"><?= mmx::icoPeople(); ?></div>
	<div id="mmx-tab-actors-1" class="mmx-tab" onclick="window.mmx.ShowTab('actors-1')"><?= mmx::icoRatingBest(); ?></div>
	<div id="mmx-tab-actors-2" class="mmx-tab" onclick="window.mmx.ShowTab('actors-2')"><?= mmx::icoRatingOkay(); ?></div>
	<div id="mmx-tab-actors-3" class="mmx-tab" onclick="window.mmx.ShowTab('actors-3')"><?= mmx::icoRatingSoSo(); ?></div>
	<div id="mmx-tab-actors-4" class="mmx-tab" onclick="window.mmx.ShowTab('actors-4')"><?= mmx::icoRatingFail(); ?></div>
	<div id="mmx-tab-movies" class="mmx-tab" onclick="window.mmx.ShowTab('movies')"><?= mmx::icoTitles(); ?></div>
	<div id="mmx-tab-movies-1" class="mmx-tab" onclick="window.mmx.ShowTab('movies-1')"><?= mmx::icoRatingBest(); ?></div>
	<div id="mmx-tab-movies-2" class="mmx-tab" onclick="window.mmx.ShowTab('movies-2')"><?= mmx::icoRatingOkay(); ?></div>
	<div id="mmx-tab-movies-3" class="mmx-tab" onclick="window.mmx.ShowTab('movies-3')"><?= mmx::icoRatingSoSo(); ?></div>
	<div id="mmx-tab-movies-4" class="mmx-tab" onclick="window.mmx.ShowTab('movies-4')"><?= mmx::icoRatingFail(); ?></div>
	<div id="mmx-tab-movies-5" class="mmx-tab" onclick="window.mmx.ShowTab('movies-5')"><?= mmx::icoRatingHalf(); ?></div>
	<div id="mmx-tab-movies-6" class="mmx-tab" onclick="window.mmx.ShowTab('movies-6')"><?= mmx::icoRatingStar(); ?></div>
	-->
</div>

<form id="mmx-search" style="display:none;" onsubmit="window.mmx.Search();return false;">
	<div class="mmx-title">16<span class="mm">mm</span></div>
	<div class="mmx-button-wrapper"><?= ButtonBox::Make('searchbutton')->WithIsRich(true)->WithValue(oxy::icoSearchGlass())->WithIsSubmit(true) ?></div>
	<?= TextBox::Make('searchstring') ?>
</form>

<form id="mmx-login" style="display:none;" onsubmit="window.mmx.Login();return false;">
	<div class="mmx-title">16<span class="mm">mm</span></div>
	<?= ButtonBox::Make('loginbutton')->WithIsRich(true)->WithValue(oxy::icoLogin())->WithIsSubmit(true) ?>
</form>

<div id="mmx-sidebar"></div>
<div id="mmx-infobar1"></div>
<div id="mmx-infobar2"></div>

<div class="mmx-slides">

</div>

<div id="mmx-run" style="display:none;"></div>
<div id="mmx-templates" style="display:none;">

    <a id="mmx-movie-tile-template" class="mmx-tile mmx-movie-tile button-load">
        <div class="image"></div>
        <div class="details" >
            <div class="col-0" ></div>
            <div class="col-1" >
                <div class="original-title"><span class="label">Original title</span><span class="value"></span></div>
                <div class="countries"><span class="label">Country</span><span class="value"></span></div>
                <div class="genres"><span class="label">Genre</span><span class="value"></span></div>
            </div>
            <div class="col-2" >
                <div class="languages"><span class="label">Language</span><span class="value"></span></div>
                <div class="runtime"><span class="label">Runtime</span><span class="value"></span></div>
                <div class="episodes"><span class="label">Episodes</span><span class="value"></span></div>
            </div>
        </div>
        <div class="main" >
            <div class="caption"></div>
            <div class="extra"></div>
        </div>
    </a>

    <a id="mmx-actor-tile-template" class="mmx-tile mmx-actor-tile button-load">
        <div class="image"></div>
        <div class="details" >
            <div class="col-0" ></div>
            <div class="col-3" >
                <div class="place-of-birth"><span class="label">Place of birth</span><span class="value"></span></div>
                <div class="year-of-birth"><span class="label">Year of birth</span><span class="value"></span></div>
                <div class="year-of-death"><span class="label">Year of death</span><span class="value"></span></div>
                <div class="countries"><span class="label">Nationality</span><span class="value"></span></div>
            </div>
        </div>
        <div class="main" >
            <div class="caption"></div>
            <div class="extra"></div>
        </div>
    </a>

    <div id="mmx-movie-info-template" class="mmx-info mmx-movie-info">
        <div class="image button-pict"></div>
        <div class="caption"></div>
        <div class="details" >
            <div class="original-title"><span class="label">Original title</span><span class="value"></span></div>
            <div class="genres"><span class="label">Genre</span><span class="value"></span></div>
            <div class="keywords"><span class="label">Keywords</span><span class="value"></span></div>
            <div class="runtime"><span class="label">Runtime</span><span class="value"></span></div>
            <div class="countries"><span class="label">Country</span><span class="value"></span></div>
            <div class="languages"><span class="label">Language</span><span class="value"></span></div>
            <div class="episodes"><span class="label">Episodes</span><span class="value"></span></div>
        </div>
        <div class="buttons">
            <a class="mmx-button button-open"><?= oxy::icoMoveRight() ?></a>
            <a class="mmx-button button-coop"><?= oxy::icoPlus() ?></a>
            <a class="mmx-button button-imdb"><?= oxy::icoInfo() ?></a>
            <div class="fclear"></div>
        </div>
        <div class="overview text"></div>
    </div>

    <div id="mmx-actor-info-template" class="mmx-info mmx-actor-info">
        <div class="image button-pict"></div>
        <div class="caption"></div>
        <div class="details" >
            <div class="place-of-birth"><span class="label">Place of birth</span><span class="value"></span></div>
            <div class="year-of-birth"><span class="label">Year of birth</span><span class="value"></span></div>
            <div class="year-of-death"><span class="label">Year of death</span><span class="value"></span></div>
            <div class="countries"><span class="label">Nationality</span><span class="value"></span></div>
        </div>
        <div class="buttons">
            <a class="mmx-button button-open"><?= oxy::icoMoveRight() ?></a>
            <a class="mmx-button button-coop"><?= oxy::icoPlus() ?></a>
            <a class="mmx-button button-imdb"><?= oxy::icoInfo() ?></a>
            <div class="fclear"></div>
        </div>
        <div class="biography text"></div>
    </div>
</div>

<div id="mmx-main"><?= Oxygen::GetContent() ?></div>
<div id="mmx-foot"></div>

<div id="mmx-fog" onclick="window.mmx.HideDialog();"></div>
<div id="mmx-dialog"></div>
















<script type="text/javascript">
/*<![CDATA[*/
window.mmx = {
    base : <?= new Js(TMDb::GetConfiguration()['images']['base_url']) ?>


    ,SelectTab:function(tab){
        jQuery('.mmx-tab').removeClass('active');
        jQuery('#mmx-tab-'+tab).addClass('active');
    }
    ,ShowSearch:function(animate) {
        var x = jQuery('#mmx-search');
        var y = jQuery('#searchstring');
        if (!x.is(':visible')) {
            if (animate===true) x.fadeIn(200); else x.show();
            y.val('');
        }
        y.focus();
    }
    ,HideSearch:function(animate) { if (animate===true) jQuery('#mmx-search').fadeOut(500); else jQuery('#mmx-search').hide(); }
    ,ToggleSearch:function(animate) { if (jQuery('#mmx-search').is(':visible')) this.HideSearch(animate); else this.ShowSearch(animate); }
    ,Search:function(){
        var q = jQuery('#searchstring').val();
        var url = <?= new Js((new Action16mm())->GetHref(['q'=>'XXX'])) ?>.replace(/XXX/,''+new Url(q));
        this.OpenTab(null,url);
    }

    ,ShowLogin:function(animate) {
        var x = jQuery('#mmx-login');
        var y = jQuery('#username');
        if (!x.is(':visible')) {
            if (animate===true) x.fadeIn(200); else x.show();
            y.val('');
        }
        y.focus();
    }

    ,FillActorTemplate:function(template,actor,extra,common){
        var xid = actor.iid;
        var s;
        var r = jQuery(template).clone();
        r.data('xid',xid);
        r.attr('id','');
        r.find('.caption').html(html(actor.Caption));
        r.find('.extra').html(html(extra));
        s = html(join(actor.Countries)); r.find('.countries').toggle(s!=='').find('.value').html(s);
        s = html(actor.PlaceOfBirth);   r.find('.place-of-birth').toggle(s!=='').find('.value').html(s);
        s = html(actor.YearOfBirth);    r.find('.year-of-birth').toggle(s!=='').find('.value').html(s);
        s = html(actor.YearOfDeath);    r.find('.year-of-death').toggle(s!=='').find('.value').html(s);
        s = html(actor.Biography);      r.find('.biography').toggle(s!=='').html(s);
        if (!empty(actor.Image)) r.find('.image').css({'background-image':'url('+actor.Image+')'});
        r.findAndSelf('.button-load').attr('href',this.GetActorCreditsUrl(actor)).click(function(e){window.mmx.SelectActor(actor,e.delegateTarget,common);return false;}).dblclick(function(){window.mmx.OpenActorCredits(actor);return false;});
        r.find('.button-open').attr('href',this.GetActorCreditsUrl(actor)).click(function(){window.mmx.OpenActorCredits(actor);return false;});
        r.find('.button-coop').attr('href',this.GetActorCooperationsUrl(actor)).click(function(){window.mmx.OpenActorCooperations(actor);return false;});
        if (actor.imdb) r.find('.button-imdb').attr('target','_blank').attr('href','http://www.imdb.com/name/'+actor.imdb);
        r.find('.button-pict').click(function(){window.mmx.ShowPictures(actor.Pictures);return false;});
        return r;
    }
    ,FillMovieTemplate:function(template,movie,extra,common){
        var xid = movie.Type+movie.iid;
        var s;
        var r = jQuery(template).clone();
        r.data('xid',xid);
        r.attr('id','');
        r.find('.caption').html(html(movie.Caption));
        r.find('.extra').html(html(extra));
        s = html(join(movie.Languages)); r.find('.languages').toggle(s!=='').find('.value').html(s);
        s = html(join(movie.Countries)); r.find('.countries').toggle(s!=='').find('.value').html(s);
        s = html(join(movie.Genres));    r.find('.genres').toggle(s!=='').find('.value').html(s);
        s = html(join(movie.Keywords));  r.find('.keywords').toggle(s!=='').find('.value').html(s);
        s = html(movie.Runtime);         r.find('.runtime').toggle(s!=='').find('.value').html(s);
        s = html(movie.OriginalTitle);   r.find('.original-title').toggle(s!=='').find('.value').html(s);
        s = html(movie.Overview);        r.find('.overview').toggle(s!=='').html(s);
        r.find('.episodes').toggle(!empty(movie.Episodes)).find('.value').html(html(movie.Episodes+(empty(movie.Seasons)?'':' in '+movie.Seasons+' '+(movie.Seasons===1?'season':'seasons'))));
        if (!empty(movie.Image)) r.find('.image').css({'background-image':'url('+movie.Image+')'});
        r.findAndSelf('.button-load').attr('href',this.GetMovieCreditsUrl(movie)).click(function(e){window.mmx.SelectMovie(movie,e.delegateTarget,common);return false;}).dblclick(function(){window.mmx.OpenMovieCredits(movie);return false;});
        r.find('.button-open').attr('href',this.GetMovieCreditsUrl(movie)).click(function(){window.mmx.OpenMovieCredits(movie);return false;});
        r.find('.button-coop').attr('href',this.GetMovieCooperationsUrl(movie)).click(function(){window.mmx.OpenMovieCooperations(movie);return false;});
        if (movie.imdb) r.find('.button-imdb').attr('target','_blank').attr('href','http://www.imdb.com/title/'+movie.imdb);
        r.find('.button-pict').click(function(){window.mmx.ShowPictures(movie.Pictures);return false;});
        return r;
    }
    ,separator:null
    ,prepend_separator:function(){ if (this.separator===null) return false; jQuery('#mmx-main').append('<div class="mmx-separator">'+this.separator+'</div>'); this.separator = null; return true; }
    ,AddSeparator:function(title){ this.separator = title===null||title===undefined ? '' : ''+title; }
    ,ResolveSeparator:function(message,icon){ if (this.prepend_separator()) jQuery('#mmx-main').append('<div class="mmx-message">'+(icon===null||icon===undefined?'':icon)+message+'</div>'); }
    ,Add:function(html){ this.prepend_separator(); jQuery('#mmx-main').append(html); }
    ,AddMovieTile:function(movie,extra,common){ this.prepend_separator(); jQuery('#mmx-main').append(this.FillMovieTemplate('#mmx-movie-tile-template',movie,extra,common)); }
    ,AddActorTile:function(actor,extra,common){ this.prepend_separator(); jQuery('#mmx-main').append(this.FillActorTemplate('#mmx-actor-tile-template',actor,extra,common)); }
    ,SelectMovie:function(movie,tile,common){
        jQuery('#mmx-infobar1').show().html( this.FillMovieTemplate('#mmx-movie-info-template',movie));
        jQuery('.mmx-tile.selected').removeClass('selected');
        jQuery(tile).addClass('selected');
				if (common !== undefined){
					var infobar2 = jQuery('#mmx-infobar2').show().html('');
					for (var i = 0; i < common.length; i++) {
						var actor = common[i];
						infobar2.append( this.FillActorTemplate('#mmx-actor-tile-template',actor) );
					}
				}
        this.Layout();
    }
    ,SelectActor:function(actor,tile,common){
        jQuery('#mmx-infobar1').show().html( this.FillActorTemplate('#mmx-actor-info-template',actor));
        jQuery('.mmx-tile.selected').removeClass('selected');
        jQuery(tile).addClass('selected');
				if (common !== undefined){
					var infobar2 = jQuery('#mmx-infobar2').show().html('');
					for (var i = 0; i < common.length; i++) {
						var movie = common[i];
						infobar2.append( this.FillMovieTemplate('#mmx-movie-tile-template',movie) );
					}
				}
        this.Layout();
    }
    ,Layout:function(){
        jQuery('#mmx-main').css({'margin-left': (jQuery('#mmx-toolbar:visible').width() + jQuery('#mmx-sidebar:visible').width()) + 'px'});
        jQuery('#mmx-infobar2').css({'right': (jQuery('#mmx-infobar1:visible').width()) + 'px'});
    }

    ,AjaxMain:function(){
        this.HideSearch();
        jQuery('body').scrollTop(0);
        jQuery('#mmx-main').html('<div class=\"noselect\" style="text-align:center;padding:100px;"><img src=\"oxy/img/ajax.gif\" /></div>');
    }
    ,ClearMain:function(){
        jQuery('#mmx-main').html('');
        jQuery('body').scrollTop(0);
    }

    ,HideDialog:function() {
        jQuery('#mmx-dialog').hide();
        jQuery('#mmx-fog').fadeOut(200);
    }
    ,ShowDialog:function() {
        jQuery('#mmx-dialog').fadeIn(200);
        jQuery('#mmx-fog').fadeIn(400);
    }
    ,ShowPictures:function(pictures){
        if (pictures.length===0) return;
        this.ShowDialog();

        var s = '';
        for (i = 0; i < pictures.length; i++)
            s += '<img src="'+this.base+'original/'+pictures[i]+'" />';

        current = 0;
        jQuery('#mmx-dialog')
          .css('background','transparent url('+window.mmx.base+'original/'+pictures[current%pictures.length]+') 50% 50% no-repeat')
        .css('background-size','contain');

        jQuery('#mmx-dialog').click(function(){
            current++;
            jQuery('#mmx-dialog')
              .css('background','#ffffff url('+window.mmx.base+'original/'+pictures[current%pictures.length]+') 50% 50% no-repeat')
            .css('background-size','contain');
        })
    }

    ,open_request : 0
    ,state:null
    ,Open:function(url,state){
        if (this.loading_page || this.loading_state)
            history.replaceState(state,null,url);
        else
            history.pushState(state,null,url);
        if (!this.loading_page){
            jQuery('#mmx-infobar1').hide();
            jQuery('#mmx-infobar2').hide();
            jQuery('#mmx-sidebar').hide();
            this.HideSearch();
            this.Layout();
            this.AjaxMain();
            var i = ++this.open_request;
            Oxygen.AjaxRequest(url,{parameters:{mode:<?=new Js(Action::MODE_HTML_FRAGMENT)?>},onSuccess:function(t){
                if (i !== window.mmx.open_request) return;
                t.responseText.evalScripts();
            }});
        }
    }
    ,GetMovieCreditsUrl:function(movie){ return <?= new Js((new ActionMovieCredits())->GetHref(['iid'=>'XXX','type'=>'YYY'])) ?>.replace(/XXX/,movie.iid).replace(/YYY/,movie.Type); }
    ,GetActorCreditsUrl:function(actor){ return <?= new Js((new ActionActorCredits())->GetHref(['iid'=>'XXX'])) ?>.replace(/XXX/,actor.iid); }
    ,GetMovieCooperationsUrl:function(movie){ return <?= new Js((new ActionMovieCooperations())->GetHref(['iid'=>'XXX'])) ?>.replace(/XXX/,movie.iid).replace(/YYY/,movie.Type); }
    ,GetActorCooperationsUrl:function(actor){ return <?= new Js((new ActionActorCooperations())->GetHref(['iid'=>'XXX'])) ?>.replace(/XXX/,actor.iid); }

    ,OpenTab:function(tab,url){
        this.SelectTab(tab);
        this.Open(url,['OpenTab',tab,url]);
    }
    ,OpenMovieCredits:function(movie){
        this.SelectTab(null);
        this.Open(this.GetMovieCreditsUrl(movie),['OpenMovieCredits',movie]);
        var x = jQuery('#mmx-sidebar').show().html( this.FillMovieTemplate('#mmx-movie-info-template',movie));
        this.Layout();
        if (!this.loading_page&&!this.loading_state) {
            var y = jQuery('#mmx-main').hide();
            x.css({opacity:0.0,left:(jQuery(window).width()-x.width())+'px'}).animate({left:'60px',opacity:1.0},200,function(){ y.fadeIn(100); });
        }
    }
    ,OpenActorCredits:function(actor){
        this.SelectTab(null);
        this.Open(this.GetActorCreditsUrl(actor),['OpenActorCredits',actor]);
        var x = jQuery('#mmx-sidebar').show().html(this.FillActorTemplate('#mmx-actor-info-template', actor));
        this.Layout();
        if (!this.loading_page&&!this.loading_state) {
            var y = jQuery('#mmx-main').hide();
            x.css({opacity:0.0,left:(jQuery(window).width()-x.width())+'px'}).animate({left:'60px',opacity:1.0},200,function(){ y.fadeIn(100); });
        }
    }
    ,OpenActorCooperations:function(actor){
        this.SelectTab(null);
        this.Open(this.GetActorCooperationsUrl(actor),['OpenActorCooperations',actor]);
        var x = jQuery('#mmx-sidebar').show().html(this.FillActorTemplate('#mmx-actor-info-template', actor));
        this.Layout();
        if (!this.loading_page&&!this.loading_state) {
            var y = jQuery('#mmx-main').hide();
            x.css({opacity:0.0,left:(jQuery(window).width()-x.width())+'px'}).animate({left:'60px',opacity:1.0},200,function(){ y.fadeIn(100); });
        }
    }
    ,OpenMovieCooperations:function(movie){
        this.SelectTab(null);
        this.Open(this.GetMovieCooperationsUrl(movie),['OpenMovieCooperations',movie]);
        var x = jQuery('#mmx-sidebar').show().html(this.FillMovieTemplate('#mmx-movie-info-template', movie));
        this.Layout();
        if (!this.loading_page&&!this.loading_state) {
            var y = jQuery('#mmx-main').hide();
            x.css({opacity:0.0,left:(jQuery(window).width()-x.width())+'px'}).animate({left:'60px',opacity:1.0},200,function(){ y.fadeIn(100); });
        }
    }

    ,loading_page:false
    ,loading_state:false
    ,OpenState:function(state){
        this.loading_state = true;
        window.mmx[state[0]].bind.apply( window.mmx[state[0]] , [window.mmx].concat(state.slice(1)) ).call(); // javascript dark magic...
        this.loading_state = false;
    }





};







Mousetrap.bind(['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'],function(){window.mmx.ShowSearch(true);});
Mousetrap.bindGlobal(['esc'],function(){window.mmx.ToggleSearch(true);});
window.onpopstate = function(ev){ window.mmx.OpenState(ev.state); };

/*]]>*/
</script>

</body></html>