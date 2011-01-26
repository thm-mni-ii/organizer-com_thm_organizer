<?php
// Wenn die Anfragen nicht durch Ajax von MySched kommt
if ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) ) {
	if ( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] != 'XMLHttpRequest' )
		die( 'Permission Denied!' );
} else
	die( 'Permission Denied!' );

class Ressource
{
	private $JDA = null;
	private $CFG = null;
	private $res = null;
	private $semID = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->res = $JDA->getRequest( "res" );
		$this->semID = $JDA->getSemID();
	}

	public function load()
	{
		if ( isset( $this->res ) && isset( $this->semID ) ) {
			$elements   = null;
			$lessons    = array( );
			$retlessons = array( );

			if ( stripos( $this->res, "VS_" ) === 0 ) {
				$elements                 = $this->getElements( $this->res, $this->semID );
				$retlessons[ "elements" ] = "";
				foreach ( $elements as $k => $v ) {
					$lessons = array_merge( $lessons, $this->getResourcePlan( $v->eid, $this->semID ) );
					if ( $retlessons[ "elements" ] == "" )
						$retlessons[ "elements" ] .= $v->eid;
					else
						$retlessons[ "elements" ] .= ";" . $v->eid;
				}
			} else
				$lessons = $this->getResourcePlan( $this->res, $this->semID );

			if(is_array($lessons))
			foreach ( $lessons as $item ) {
				$key = $item->lid . " " . $item->tpid;
				if ( !isset( $retlessons[ $key ] ) )
					$retlessons[ $key ] = array( );
				$retlessons[ $key ][ "type" ]    = $item->type;
				$retlessons[ $key ][ "id" ]      = $item->id;
				$retlessons[ $key ][ "subject" ] = $item->subject;
				$retlessons[ $key ][ "dow" ]     = $item->dow;
				$retlessons[ $key ][ "block" ]   = $item->block;

				if ( isset( $retlessons[ $key ][ "clas" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "clas" ] );
					if ( !in_array( $item->clas, $arr ) )
						$retlessons[ $key ][ "clas" ] = $retlessons[ $key ][ "clas" ] . " " . $item->clas;
				} else
					$retlessons[ $key ][ "clas" ] = $item->clas;

				if ( isset( $retlessons[ $key ][ "doz" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "doz" ] );
					if ( !in_array( $item->doz, $arr ) )
						$retlessons[ $key ][ "doz" ] = $retlessons[ $key ][ "doz" ] . " " . $item->doz;
				} else
					$retlessons[ $key ][ "doz" ] = $item->doz;

				if ( isset( $retlessons[ $key ][ "room" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "room" ] );
					if ( !in_array( $item->room, $arr ) )
						$retlessons[ $key ][ "room" ] = $retlessons[ $key ][ "room" ] . " " . $item->room;
				} else
					$retlessons[ $key ][ "room" ] = $item->room;

				$retlessons[ $key ][ "category" ] = $item->category;
				$retlessons[ $key ][ "key" ]      = $key;
				$retlessons[ $key ][ "owner" ]    = "gpuntis";
				$retlessons[ $key ][ "showtime" ] = "none";
				$retlessons[ $key ][ "etime" ]    = null;
				$retlessons[ $key ][ "stime" ]    = null;
				$retlessons[ $key ][ "name" ]     = $item->name;
				$retlessons[ $key ][ "desc" ]     = $item->description;
				$retlessons[ $key ][ "cell" ]     = "";
				$retlessons[ $key ][ "css" ]      = "";
				$retlessons[ $key ][ "longname" ] = $item->longname;
			}

			$retlessons = $this->getAllRes( $retlessons, $this->semID );
			return array("success"=>true,"data"=>$retlessons );
		}
	}

	private function getElements( $id, $sid )
	{
		$query = "SELECT eid " . "FROM #__giessen_scheduler_virtual_schedules_elements " . "WHERE vid = '" . $id . "' " . "AND sid = '" . $sid . "'";
		$ret   = $this->JDA->query( $query );
		return $ret;
	}

	private function getAllRes( $less, $classemesterid )
	{
		foreach ( $less as $lesson ) {
			$query = "SELECT CONCAT(CONCAT(jos_giessen_scheduler_lessons.lid, ' '),jos_giessen_scheduler_lessonperiods.tpid) AS mykey, cid, rid, tid, ltype, jos_giessen_scheduler_timeperiods.day AS dow, period AS block, oname AS name, jos_giessen_scheduler_objects.oalias AS description, jos_giessen_scheduler_objects.oid AS id, (SELECT 'cyclic') AS type
	          FROM jos_giessen_scheduler_lessons
	          INNER JOIN jos_giessen_scheduler_lessonperiods
	          ON jos_giessen_scheduler_lessons.lid = jos_giessen_scheduler_lessonperiods.lid
	          INNER JOIN jos_giessen_scheduler_timeperiods
	          ON jos_giessen_scheduler_lessonperiods.tpid = jos_giessen_scheduler_timeperiods.tpid
	          INNER JOIN jos_giessen_scheduler_objects
	          ON jos_giessen_scheduler_lessonperiods.lid = jos_giessen_scheduler_objects.oid
	          WHERE otype = 'lesson' AND jos_giessen_scheduler_lessons.sid = '$classemesterid' AND jos_giessen_scheduler_lessons.lid IN ('" . $lesson[ "id" ] . "');";

			$ret = $this->JDA->query( $query );

			$lessons = array( );

			if ( isset( $ret ) )
				if ( is_array( $ret ) )
					foreach ( $ret as $v ) {
						$key = $v->mykey;
						if ( !isset( $lessons[ $key ] ) )
							$lessons[ $key ] = array( );
						$lessons[ $key ][ "category" ] = $v->ltype;
						if ( isset( $lessons[ $key ][ "clas" ] ) ) {
							$arr = explode( " ", $lessons[ $key ][ "clas" ] );
							if ( !in_array( $v->cid, $arr ) )
								$lessons[ $key ][ "clas" ] = $lessons[ $key ][ "clas" ] . " " . $v->cid;
						} else
							$lessons[ $key ][ "clas" ] = $v->cid;

						if ( isset( $lessons[ $key ][ "doz" ] ) ) {
							$arr = explode( " ", $lessons[ $key ][ "doz" ] );
							if ( !in_array( $v->tid, $arr ) )
								$lessons[ $key ][ "doz" ] = $lessons[ $key ][ "doz" ] . " " . $v->tid;
						} else
							$lessons[ $key ][ "doz" ] = $v->tid;

						if ( isset( $lessons[ $key ][ "room" ] ) ) {
							$arr = explode( " ", $lessons[ $key ][ "room" ] );
							if ( !in_array( $v->rid, $arr ) )
								$lessons[ $key ][ "room" ] = $lessons[ $key ][ "room" ] . " " . $v->rid;
						} else
							$lessons[ $key ][ "room" ] = $v->rid;

						$lessons[ $key ][ "dow" ]      = $v->dow;
						$lessons[ $key ][ "block" ]    = $v->block;
						$lessons[ $key ][ "name" ]     = $v->name;
						$lessons[ $key ][ "desc" ]     = $v->description;
						$lessons[ $key ][ "cell" ]     = "";
						$lessons[ $key ][ "css" ]      = "";
						$lessons[ $key ][ "owner" ]    = "gpuntis";
						$lessons[ $key ][ "showtime" ] = "none";
						$lessons[ $key ][ "etime" ]    = null;
						$lessons[ $key ][ "stime" ]    = null;
						$lessons[ $key ][ "key" ]      = $key;
						$lessons[ $key ][ "id" ]       = $v->id;
						$lessons[ $key ][ "subject" ]  = $v->id;
						$lessons[ $key ][ "type" ]     = $v->type;
					}

			foreach ( $lessons as $l ) {
				if ( $lesson[ "key" ] == $l[ "key" ] ) {
					$less[ $lesson[ "key" ] ][ "clas" ] = $l[ "clas" ];
					$less[ $lesson[ "key" ] ][ "doz" ]  = $l[ "doz" ];
					$less[ $lesson[ "key" ] ][ "room" ] = $l[ "room" ];
				}
			}
		}
		return $less;
	}

	private function getResourcePlan( $ressourcename, $fachsemester )
	{
		$query = "SELECT lp.lid, lp.tpid, lo.oid AS id, lo.oalias AS description, lo.oid AS subject, l.ltype AS category, lo.oname AS name, co.oid AS clas, tobj.oid AS doz, ro.oid AS room, day AS dow, period AS block, (SELECT 'cyclic') AS type, ";
		if ($this->JDA->isComponentavailable("com_giessenlsf"))
		{
			$query .= " modultitel AS longname FROM jos_giessen_scheduler_objects AS lo LEFT JOIN #__giessen_lsf_modules AS mo ON lo.oalias = mo.modulnummer ";
		}
		else
		{
			$query .= " '' AS longname FROM jos_giessen_scheduler_objects AS lo ";
		}
		$query .= " INNER JOIN jos_giessen_scheduler_lessons AS l
	             ON lo.oid = l.lid
	          INNER JOIN jos_giessen_scheduler_lessonperiods AS lp
	             ON l.lid = lp.lid
	          INNER JOIN jos_giessen_scheduler_objects as ro
	             ON lp.rid = ro.oid
	          INNER JOIN jos_giessen_scheduler_objects as tobj
	             ON lp.tid = tobj.oid
	          INNER JOIN jos_giessen_scheduler_objects AS co
	             ON co.oid = l.cid
	          INNER JOIN jos_giessen_scheduler_timeperiods AS tp
	             ON lp.tpid = tp.tpid
	          WHERE lo.otype = 'lesson' AND lo.sid = '$fachsemester' AND l.sid = '$fachsemester' AND lp.sid = '$fachsemester' AND tp.sid = '$fachsemester'
	             AND ( co.oid = '".$ressourcename."' OR tobj.oname = '".$ressourcename."' OR tobj.oid = '".$ressourcename."' OR ro.oid = '".$ressourcename."' )";
		$hits  = $this->JDA->query( $query );

		return $hits;
	}
}
?>