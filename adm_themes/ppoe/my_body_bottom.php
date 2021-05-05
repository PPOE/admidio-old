<?php
/*
 * Hier koennen Sie Ihren HTML-Code einbauen, der am Ende des <body> Bereichs
 * einer Admidio-Modul-Seite erscheinen soll.
*/

// Link zur Moduluebersicht
if(strpos($_SERVER['REQUEST_URI'], 'index.php') === false)
{
    echo '<div style="text-align: center; margin-top: 5px;">
        <a href="'.$gHomepage.'">'.$gL10n->get('SYS_BACK_TO_MODULE_OVERVIEW').'</a>
    </div>';
}
?>
			</div>
		</div>
	</div>
</div>
<div class="section subcontent">
  <div class="row">
      </div>
</div>
<div class="section footer">
    <div class="row">
        <div class="first-footer-widget-area">
        	<div class="skin">
        		<div class="widget">
        			<ul class='xoxo blogroll'>
        				<li>
        					<div class="skin">
								<div class="widget">
                               	  <h3 class="widget-title">Mitgliederverwaltung</h3>
									<p>
										<span class="admidio"><a href="https://www.piratenpartei.at/" target="_blank">Piratenpartei Österreichs</a> - realisiert mit <a href="http://www.admidio.org" target="_blank" style="background:none;padding:0;margin:0;">admidio</a>,&nbsp;&copy; 2004 - <?php echo date("Y"); ?>&nbsp;&nbsp;<?php echo $gL10n->get('SYS_ADMIDIO_TEAM'); ?></span>, all <span class="github">modifications on <a href="https://github.com/PPOE/admidio" target="_blank">gitHub</a> </span>
									</p>
								</div>
<style type="text/css">
/* Bundesländer Box */
.footer .bundeslaender {}
.footer .widget li.lo { border-radius:5px; padding:0; }
.footer .lo a { text-decoration:none; display:block; width:100%; border-radius:5px; background:rgba(255,255,255,0.2); line-height:32px; font-size:15px; margin-bottom:3px; }
.footer .lo a:hover { border-radius:5px; }
.footer .lo a span.landesname { vertical-align:middle; }
.footer .lo a span.landeswappen { width:30px; display:inline-block; text-align:center; vertical-align:top; } 
.footer .lo a img { margin:0; padding:0; max-width:40px; height:28px; vertical-align:bottom; }
</style>
        						<div class="widget">
                                    <h2>Bundesländer</h2>
                                    <div class="textwidget">
                                        <ul class="bundeslaender">
                                            <li class="lo"><a title="Burgenland" href="http://burgenland.piratenpartei.at" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Burgenland.png" alt="Burgenland" height="40"></span><span class="landesname"> Burgenland</span></a></li>
                                            <li class="lo"><a title="Kärnten" href="http://kaernten.piratenpartei.at" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Kaernten.png" alt="Kärnten" height="40"></span><span class="landesname"> Kärnten</span></a></li>
                                            <li class="lo"><a title="Niederösterreich" href="http://niederoesterreich.piratenpartei.at/" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Niederoesterreich.png" alt="Niederösterreich" height="40"></span><span class="landesname"> Niederösterreich</span></a></li>
                                            <li class="lo"><a title="Oberösterreich" href="http://oberoesterreich.piratenpartei.at/" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Oberoesterreich.png" alt="Oberösterreich" height="40"></span><span class="landesname"> Oberösterreich</span></a></li>
                                            <li class="lo"><a title="Salzburg" href="http://salzburg.piratenpartei.at/" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Salzburg.png" alt="Salzburg" height="40"></span><span class="landesname"> Salzburg</span></a></li>
                                            <li class="lo"><a title="Steiermark" href="http://steiermark.piratenpartei.at/" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Steiermark.png" alt="Steiermark" height="40"></span><span class="landesname"> Steiermark</span></a></li>
                                            <li class="lo"><a title="Tirol" href="https://www.piratenpartei.at/" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Tirol.png" alt="Tirol" height="40"></span><span class="landesname"> Tirol</span></a></li>
                                            <li class="lo"><a title="Vorarlberg" href="http://vorarlberg.piratenpartei.at/" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Vorarlberg.png" alt="Vorarlberg" height="40"></span><span class="landesname"> Vorarlberg</span></a></li>
                                            <li class="lo"><a title="Wien" href="http://wien.piratenpartei.at/" target="_blank"><span class="landeswappen"><img src="https://archiv.piratenpartei.at/wp-content/uploads/laender/Wien.png" alt="Wien" height="40"></span><span class="landesname"> Wien</span></a></li>
                                        </ul>
                                    </div>
                                </div>
       						</div>
       					</li>
       				</ul>
        		</div>
        	</div>
        </div>
        <div class="second-footer-widget-area">
			<div class="skin">
				<div class="widget">
					<h3 class="widget-title">Piratenpartei Österreichs</h3>
					<div class="menu-subnav-container">
						<ul id="menu-subnav" class="menu">
							<li id="menu-item-319" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-319"><a target="_blank" href="http://piratenpartei.at/rechtliches/impressum/">Impressum</a></li>
							<li id="menu-item-328" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-328"><a target="_blank" href="http://piratenpartei.at/rechtliches/datenschutzerklaerung/">Datenschutzerklärung</a></li>
							<li id="menu-item-320" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-320"><a target="_blank" href="http://creativecommons.org/licenses/by-sa/3.0/de/deed.de">CC-BY-SA 3.0</a></li>
							<li id="menu-item-304" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-304"><a target="_blank" href="http://piratenpartei.at/rechtliches/kontakt/">Kontakt</a></li>
							<li id="menu-item-324" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-324"><a target="_blank" href="http://piratenpartei.at/rechtliches/credits/">Credits</a></li>
							</ul>
						</div>
					</div>
					<br>
				</div>
			</div>
	    </div>
	</div>
<?php

//include(SERVER_PATH."/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag.php");
?>
