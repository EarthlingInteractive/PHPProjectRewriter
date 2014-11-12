<?php

class EarthIT_PHPProjectRewriter
{
	public function rewrite( EarthIT_PHPProjectRewriter_Project $inProj, EarthIT_PHPProjectRewriter_Project $outProj ) {
		$worker = new EarthIT_PHPProjectRewriter_Worker($inProj, $outProj);
		$worker->run();
	}
}
