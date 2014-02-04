
<!-- Hier koennen Sie Ihren HTML-Code einbauen, der am Ende des <body> Bereichs
     einer Admidio-Modul-Seite erscheinen soll.
-->
<?php
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
								<div class="widget"><h3 class="widget-title">Mitgliederverwaltung</h3>
									<p>
										<span class="admidio">realisiert mit <a href="http://www.admidio.org" target="_blank" style="background:none;padding:0;margin:0;">admidio</a>,&nbsp;&copy; 2004 - 2012&nbsp;&nbsp;<?php echo $gL10n->get('SYS_ADMIDIO_TEAM'); ?></span>
									</p>
								</div>
        						<div class="widget">
        							<h3 class="widget-title">Piratenparteien International</h3>
        							<ul class='xoxo blogroll'>
        								<li><a href="http://www.partido-pirata.com.ar" rel="co-worker colleague" target="_blank">Argentinien</a></li>
        								<li><a href="http://pirateparty.org.au/" rel="co-worker colleague" target="_blank">Australien</a></li>
        								<li><a href="http://pirateparty.be/" rel="co-worker colleague" target="_blank">Belgien</a></li>
        								<li><a href="http://www.facebook.com/pages/Partido-Pirata-Boliviano/179377808754819" rel="co-worker colleague" target="_blank">Bolivien</a></li>
        								<li><a href="http://www.facebook.com/group.php?gid=43311696154" rel="co-worker colleague" target="_blank">Bosnien und Herzegowina</a></li>
        								<li><a href="http://partidopirata.org/" rel="co-worker colleague" target="_blank">Brasilien</a></li>
        								<li><a href="http://piratskapartia.bg/" rel="co-worker colleague" target="_blank">Bulgarien</a></li>
        								<li><a href="http://www.partidopirata.cl/" rel="co-worker colleague" target="_blank">Chile</a></li>
        								<li><a href="https://www.facebook.com/CNPirates" rel="co-worker colleague" target="_blank">China</a></li>
        								<li><a href="http://www.facebook.com/group.php?gid=117638966194" rel="co-worker colleague" target="_blank">Costa Rica</a></li>
        								<li><a href="http://www.piratpartiet.dk" rel="co-worker colleague" target="_blank">Dänemark</a></li>
        								<li><a href="http://www.piratenpartei.de/" rel="co-worker colleague" target="_blank">Deutschland</a></li>
        								<li><a href="http://www.facebook.com/pages/Partido-Pirata-de-Ecuador/106331816101687" rel="co-worker colleague" target="_blank">Ecuador</a></li>
        								<li><a href="http://www.facebook.com/pages/Partido-Pirata-de-El-Salvador/186339448051845" rel="co-worker colleague" target="_blank">El Salvador</a></li>
        								<li><a href="http://piraadipartei.ee/" rel="co-worker colleague" target="_blank">Estland</a></li>
        								<li><a href="http://www.piraattipuolue.fi" rel="co-worker colleague" target="_blank">Finnland</a></li>
        								<li><a href="http://www.partipirate.org" rel="co-worker colleague" target="_blank">Frankreich</a></li>
        								<li><a href="http://www.pirateparty.gr/" rel="co-worker colleague" target="_blank">Griechenland</a></li>
        								<li><a href="http://partidopirata.org.gt/" rel="co-worker colleague" target="_blank">Guatemala</a></li>
        								<li><a href="http://www.facebook.com/group.php?gid=89128912367" rel="co-worker colleague" target="_blank">Irland</a></li>
        								<li><a href="http://votopirata.it/" rel="co-worker colleague" target="_blank">Italien</a></li>
        								<li><a href="http://www.pirateparty.ca/" rel="co-worker colleague" target="_blank">Kanada</a></li>
        								<li><a href="http://pirateparty.kz/" rel="co-worker colleague" target="_blank">Kasachstan</a></li>
        								<li><a href="http://pp.interlecto.net/" rel="co-worker colleague" target="_blank">Kolumbien</a></li>
        								<li><a href="http://pirati.hr" rel="co-worker colleague" target="_blank">Kroatien</a></li>
        								<li><a href="http://www.piratupartija.lv/" rel="co-worker colleague" target="_blank">Lettland</a></li>
        								<li><a href="http://piratupartija.lt/" rel="co-worker colleague" target="_blank">Litauen</a></li>
        								<li><a href="http://piratepartei.lu/" rel="co-worker colleague" target="_blank">Luxemburg</a></li>
        								<li><a href="http://www.facebook.com/PPMaroc" rel="co-worker colleague" target="_blank">Marokko</a></li>
        								<li><a href="http://partidopiratamexicano.org/" rel="co-worker colleague" target="_blank">Mexiko</a></li>
        								<li><a href="http://pirateparty.org.nz/" rel="co-worker colleague" target="_blank">Neuseeland</a></li>
        								<li><a href="http://piratenpartij.nl" rel="co-worker colleague" target="_blank">Niederlande</a></li>
        								<li><a href="http://www.facebook.com/group.php?gid=126296200695" rel="co-worker colleague" target="_blank">Norwegen</a></li>
        								<li><a href="http://piratenpartei.at" rel="co-worker colleague" target="_blank">Österreich</a></li>
        								<li><a href="http://www.facebook.com/group.php?gid=120833578560" rel="co-worker colleague" target="_blank">Panama</a></li>
        								<li><a href="http://wiki.freeculture.org/Pirata" rel="co-worker colleague" target="_blank">Peru</a></li>
        								<li><a href="http://www.pp-international.net" rel="co-worker colleague" target="_blank">Pirate Parties International</a></li>
        								<li><a href="http://blog.pirates-without-borders.org" title="Pirates Without Borders">Pirates Without Borders</a></li>
        								<li><a href="http://www.partiapiratow.org.pl/" rel="co-worker colleague" target="_blank">Polen</a></li>
        								<li><a href="http://www.partidopiratapt.eu/" rel="co-worker colleague" target="_blank">Portugal</a></li>
        								<li><a href="http://pirateparty.kr/" rel="co-worker colleague" target="_blank">Republik Korea</a></li>
        								<li><a href="http://www.partidulpirat.ro/" rel="co-worker colleague" target="_blank">Rumänien</a></li>
        								<li><a href="http://pirate-party.ru/" rel="co-worker colleague" target="_blank">Russland</a></li>
        								<li><a href="http://www.piratpartiet.se" rel="co-worker colleague" target="_blank">Schweden</a></li>
        								<li><a href="http://pirateparty.ch/" rel="co-worker colleague" target="_blank">Schweiz</a></li>
        								<li><a href="http://piratskapartija.com" rel="co-worker colleague" target="_blank">Serbien</a></li>
        								<li><a href="http://piratskastrana.sk/" rel="co-worker colleague" target="_blank">Slowakei</a></li>
        								<li><a href="http://piratskastranka.net/" rel="co-worker colleague" target="_blank">Slowenien</a></li>
        								<li><a href="http://www.partidopirata.es" rel="co-worker colleague" target="_blank">Spanien</a></li>
        								<li><a href="http://pirata.cat/" rel="co-worker colleague" target="_blank">Spanien :: Katalonien</a></li>
        								<li><a href="http://web.archive.org/web/20080916162325/http://pp.org.za/" rel="co-worker colleague" target="_blank">Südafrika</a></li>
        								<li><a href="http://www.facebook.com/pages/Pirate-Party-Taiwan/200678743528" rel="co-worker colleague" target="_blank">Taiwan</a></li>
        								<li><a href="http://www.pirati.cz/" rel="co-worker colleague" target="_blank">Tschechische Republik</a></li>
        								<li><a href="http://partipirate-tunisie.org/" rel="co-worker colleague" target="_blank">Tunesien</a></li>
        								<li><a href="http://korsanparti.org/" rel="co-worker colleague" target="_blank">Türkei</a></li>
        								<li><a href="http://www.pp-ua.org" rel="co-worker colleague" target="_blank">Ukraine</a></li>
        								<li><a href="http://partidopirata.org.uy/" rel="co-worker colleague" target="_blank">Uruguay</a></li>
        								<li><a href="http://www.partidopiratadevenezuela.org" rel="co-worker colleague" target="_blank">Venezuela</a></li>
        								<li><a href="http://www.pirate-party.us" rel="co-worker colleague" target="_blank">Vereinigte Staaten</a></li>
        								<li><a href="http://www.pirateparty.org.uk" rel="co-worker colleague" target="_blank">Vereinigtes Königreich</a></li>
        								<li><a href="http://ppb.by" rel="co-worker colleague" target="_blank">Weißrussland</a></li>
        								<li><a href="http://www.piratepartycyprus.com/" rel="co-worker colleague" target="_blank">Zypern</a></li>
       								</ul>
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
							<li id="menu-item-319" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-319"><a href="http://piratenpartei.at/rechtliches/impressum/">Impressum</a></li>
							<li id="menu-item-328" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-328"><a href="http://piratenpartei.at/rechtliches/datenschutzerklaerung/">Datenschutzerklärung</a></li>
							<li id="menu-item-320" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-320"><a target="_blank" href="http://creativecommons.org/licenses/by-sa/3.0/de/deed.de">CC-BY-SA 3.0</a></li>
							<li id="menu-item-304" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-304"><a href="http://piratenpartei.at/rechtliches/kontakt/">Kontakt</a></li>
							<li id="menu-item-324" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-324"><a href="http://piratenpartei.at/rechtliches/credits/">Credits</a></li>
							<li id="menu-item-329" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-329"><a target="_blank" href="http://piratenpartei.at/feed/">RSS Feed</a></li>
							<li id="menu-item-305" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-305"><a target="_blank" href="http://piratenpartei.at/wp-admin/">Login</a></li>
							</ul>
						</div>
					</div>
					<br>
					<div class="widget">
						<h3 class="widget-title">Landesorganisationen</h3>
						<ul class='xoxo blogroll'>
							<li><a href="http://burgenland.piratenpartei.at" title="Piratenpartei Burgenland" target="_blank">Burgenland</a></li>
							<li><a href="http://kaernten.piratenpartei.at" title="Piratenpartei Kärnten" target="_blank">Kärnten</a></li>
							<li><a href="http://niederoesterreich.piratenpartei.at" title="Piratenpartei Niederösterreich" target="_blank">Niederösterreich</a></li>
							<li><a href="http://oberoesterreich.piratenpartei.at" title="Piratenpartei Oberösterreich" target="_blank">Oberösterreich</a></li>
							<li><a href="http://salzburg.piratenpartei.at/" title="Piratenpartei Salzburg" target="_blank">Salzburg</a></li>
							<li><a href="http://steiermark.piratenpartei.at/" title="Landesorganisation der Piratenpartei">Steiermark</a></li>
							<li><a href="http://www.piratenpartei-tirol.org" title="Piratenpartei Tirol" target="_blank">Tirol</a></li>
							<li><a href="http://vorarlberg.piratenpartei.at" title="Piratenpartei Vorarlberg" target="_blank">Vorarlberg</a></li>
							<li><a href="http://wien.piratenpartei.at" title="Piratenpartei Wien" target="_blank">Wien</a></li>
						</ul>
					</div>
				</div>
			</div>
	    </div>
	</div>
<?php

//include(SERVER_PATH."/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag.php");
?>
