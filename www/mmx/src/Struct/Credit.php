<?php

class Credit {

	public $_actor;
	public $_movie;
	public $_time;
	public $Roles;

	public function __construct(Actor $actor, Movie $movie, $info, $time ) {
		$this->_actor = $actor;
		$this->_movie = $movie;
		$this->set_info($time,$info);
	}
	private function set_info($time,$info){
		$this->_time = $time;
		$this->Roles = array_map( function($x){ return self::DecodeJob($x); } , $info );
	}
	public function GetCaption(){ return implode(', ',$this->Roles); }



	public static function EncodeJob($txt) {
		if (self::$inverse_jobs===null) self::$inverse_jobs = array_combine(array_values(self::$jobs),array_keys(self::$jobs));
		$txt = str_replace(['himself','herself'],['Himself','Herself'],$txt);
		return array_key_exists($txt,self::$inverse_jobs) ? self::$inverse_jobs[$txt] : $txt;
	}
	public static function DecodeJob($num) {
		return array_key_exists($num,self::$jobs) ? self::$jobs[$num] : '';
	}

	private static $inverse_jobs = null;
	private static $jobs = [0=>'',1=>'Crew',-1=>'Cast',-2=>'Himself',-3=>'Herself',-4=>'Himself (voice)',-5=>'Herself (voice)',-6=>'Himself (archive footage)',-7=>'Herself (archive footage)',-8=>'Created by'
		,'Other','Screenplay','Author','Novel','Characters','Theatre Play','Adaptation','Dialogue','Writer','Storyboard','Original Story','Scenario Writer','Screenstory','Musical','Idea','Story','Creative Producer','Teleplay','Texte','Opera','Co-Writer','Director','Script Supervisor','Layout','Script Coordinator','Special Guest Director','Actor','Stunt Double','Voice','Cameo','Special Guest','Director of Photography','Underwater Camera','Camera Operator','Still Photographer','Camera Department Manager','Camera Supervisor','Camera Technician','Grip','Steadicam Operator','Additional Camera','Camera Intern','Additional Photography','Helicopter Camera','Editor','Supervising Film Editor','Additional Editing','Editorial Manager','First Assistant Editor','Additional Editorial Assistant','Editorial Coordinator','Editorial Production Assistant','Editorial Services','Dialogue Editor','Archival Footage Coordinator','Archival Footage Research'
		,'Color Timer','Digital Intermediate','Production Design','Art Direction','Set Decoration','Set Designer','Conceptual Design','Interior Designer','Settings','Assistant Art Director','Art Department Coordinator','Art Department Manager','Sculptor','Art Department Assistant','Background Designer','Co-Art Director','Set Decoration Buyer','Production Illustrator','Standby Painter','Location Scout','Leadman','Greensman','Gun Wrangler','Construction Coordinator','Construction Foreman','Lead Painter','Sign Painter','Costume Design','Makeup Artist','Hairstylist','Set Dressing Artist','Set Dressing Supervisor','Set Dressing Manager','Set Dressing Production Assistant','Facial Setup Artist','Hair Setup','Costume Supervisor','Set Costumer','Makeup Department Head','Wigmaker','Maske','Shoe Design','Co-Costume Designer','Producer','Executive Producer','Casting','Production Manager','Unit Production Manager','Line Producer','Location Manager'
		,'Production Supervisor','Production Accountant','Production Office Coordinator','Finance', 'Executive Consultant','Character Technical Supervisor','Development Manager','Administration','Executive In Charge Of Post Production','Herstellungsleitung','Produzent','Production Director','Executive In Charge Of Production','Publicist','Original Music Composer','Sound Designer','Sound Editor','Sound Director','Sound mixer','Music Editor','Sound Effects Editor','Production Sound Mixer','Additional Soundtrack','Supervising Sound Editor','Supervising Sound Effects Editor','Sound Re-Recording Mixer','Recording Supervision','Boom Operator','Sound Montage Associate','Songs','Music','ADR & Dubbing','Sound Engineer','Foley','Additional Music Supervisor','First Assistant Sound Editor','Scoring Mixer','Dolby Consultant','Tonbearbeitung','Animation','Visual Effects','Chief Technician / Stop-Motion Expert','Creature Design','Shading','Modeling'
		,'CG Painter','Visual Development','Animation Manager','Animation Director','Fix Animator','Animation Department Coordinator','Animation Fix Coordinator','Animation Production Assistant','Visual Effects Supervisor','Mechanical & Creature Designer','Battle Motion Coordinator','Animation Supervisor','VFX Supervisor','Cloth Setup','VFX Artist','CG Engineer','24 Frame Playback','Imaging Science','I/O Supervisor','Visual Effects Producer','VFX Production Coordinator','I/O Manager','Additional Effects Development','Color Designer','Simulation & Effects Production Assistant','Simulation & Effects Artist','Special Effects','Post Production Supervisor','Second Unit','Choreographer','Stunts','Sound Recordist','Stunt Coordinator','Special Effects Coordinator','Supervising Technical Director','Supervising Animator','Production Artist','Sequence Leads','Second Film Editor','Temp Music Editor','Temp Sound Editor','Sequence Supervisor'
		,'Software Team Lead','Software Engineer','Documentation & Support','Machinist','Photoscience Manager','Department Administrator','Schedule Coordinator','Supervisor of Production Resources','Production Office Assistant','Information Systems Manager','Systems Administrators & Support','Projection','Post Production Assistant','Sound Design Assistant','Mix Technician','Motion Actor','Sets & Props Supervisor','Compositors','Tattooist','Sets & Props Artist','Motion Capture Artist','Sequence Artist','Mixing Engineer','Special Sound Effects','Post-Production Manager','Dialect Coach','Picture Car Coordinator','Property Master','Cableman','Set Production Assistant','Video Assist Operator','Unit Publicist','Set Medic','Stand In','Transportation Coordinator','Transportation Captain','Supervising Art Director','Stunts Coordinator','Post Production Consulting','Production Intern','Utility Stunts','Actor\'s Assistant','Set Production Intern'
		,'Production Controller','Studio Teachers','Chef','Craft Service','Scenic Artist','Propmaker','Prop Maker','Transportation Co-Captain','Driver','Security','Second Unit Cinematographer','Loader','Manager of Operations','Quality Control Supervisor','Legal Services','Public Relations','Score Engineer','Translator','Title Graphics','Telecine Colorist','Comic-Zeichner','Animatronic and Prosthetic Effects','Martial Arts Choreographer','Cinematography','Steadycam','Regieassistenz','Executive Visual Effects Producer','Visual Effects Design Consultant','Digital Effects Supervisor','Digital Producer','CG Supervisor','Visual Effects Art Director','Visual Effects Editor','executive in charge of Finance','Associate Choreographer','Makeup Effects','Maskenbildner','Redaktion','treatment','Dramaturgie','Lighting Camera','Technical Supervisor','CGI Supervisor','Creative Consultant','Script','Executive Music Producer','Tongestaltung','Commissioning Editor'
		,'Klimatechnik','Tiertrainer','Additional Writing','Additional Music','Poem','Thanks','Szenografie','Mischung','Titelgestaltung','Musikmischung','Creator','Additional Dialogue','Video Game','Graphic Novel Illustrator','Series Writer','Radio Play','Lighting Technician','Best Boy Electric','Gaffer','Rigging Gaffer','Lighting Supervisor','Lighting Manager','Directing Lighting Artist','Master Lighting Artist','Lighting Artist','Lighting Coordinator','Lighting Production Assistant','Best Boy Electrician','Electrician','Rigging Grip'
	];

}