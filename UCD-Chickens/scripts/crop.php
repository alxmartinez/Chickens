<?php

/*
 * Crop info
 *
 * Usage:
 *
 * get the crop info
 * <SERVER ADDR>/?id=1
 *
 *
 * Note - Only for testing and debuging, no security or input sanitation etc.
 *
 */

// NOTE - Change these for your own pg sever / user

//$db = new PDO('pgsql:dbname=ucddb;host=localhost;user=postgres;password=password');
$db = new PDO('pgsql:dbname=db1271257_cybar;host=postgresql68int.cp.blacknight.com;user=u1271257_cybar;password=^Q>8k3M.Wi');

// check that all the id is present
if (isset($_GET['id'])) {

	//ceheck that the var is an int
	if (is_numeric($_GET['id'])) {

		// $_GET vars
		// NOTE - This will need to be sanitised
		$id = $_GET['id'];

		$sql = "SELECT * FROM crop_information WHERE id = $id;";

		$rs = $db -> query($sql);

		if (!$rs) {

			echo "An SQL error occured.\n";

			exit ;

		} else {

			$rows = array();

			while ($r = $rs -> fetch(PDO::FETCH_ASSOC)) {

				// get house info
				$sql = "SELECT * FROM house_information WHERE id = $r[house_id];";

				// remove house id from array
				//unset($r[house_id]);

				$hrs = $db -> query($sql);

				while ($hr = $hrs -> fetch(PDO::FETCH_ASSOC)) {// house

					// get farm info
					$sql = "SELECT * FROM farm_information WHERE id = $hr[farm_id];";

					// remove farm id from array
					unset($r['farm_id']);

					$frs = $db -> query($sql);

					while ($fr = $frs -> fetch(PDO::FETCH_ASSOC)) {// farm

						// remove farm id from array
						unset($hr['farm_id']);
						$hr['farm'] = $fr;
					}

					// get bosca info
					$sql = "SELECT * FROM bosca_information WHERE house_id = $hr[id];";

					$birs = $db -> query($sql);

					$boscas = array();

					while ($bir = $birs -> fetch(PDO::FETCH_ASSOC)) {// boscas

						// remove farm id from array
						//unset($hr[farm_id]);
						//$hr[farm] = $fr;

						// get the eading for ach of the phnomenons
						// only 1 to 5 is curently implemented

						for ($i = 1; $i <= 5; $i++) {

							// get sensor info
							$sql = "SELECT * FROM sensor_information as s_info JOIN phenomenon_information AS p_info ON s_info.phenomenon_id = p_info.id  WHERE bosca_id = $bir[id] AND phenomenon_id = $i;";

							//sensor_information as s_info ON b_info.id = s_info.bosca_id JOIN bosca_readings as b_readings ON b_readings.sensor_id = s_info.id JOIN phenomenon_information AS p_info ON s_info.phenomenon_id = p_info.id

							$sirs = $db -> query($sql);

							$sensors = array();

							while ($sir = $sirs -> fetch(PDO::FETCH_ASSOC)) {// boscas

								$sensors = $sir;
								//print_r($sir[phenomenon]);
								//exit;

								$phenomenon = substr($sir['phenomenon'], 1, -1);

								unset($sensors['phenomenon']);
								unset($sensors['bosca_id']);

								//$sql = "SELECT * FROM bosca_readings AS b_readings WHERE b_readings.unix_timestamp between 13921439 AND 1392148941 AND phenomenon_id = $sir[phenomenon_id]";

								// Temporarly added limit
								$sql = "SELECT id, unix_timestamp, value, sensor_id FROM bosca_readings AS b_readings WHERE b_readings.unix_timestamp between $r[unix_startdate] AND $r[unix_killdate] AND phenomenon_id = $sir[phenomenon_id] ORDER BY b_readings.unix_timestamp DESC LIMIT 20;";

								$readings = array();

								$brrs = $db -> query($sql);

								while ($brr = $brrs -> fetch(PDO::FETCH_ASSOC)) {// bosca readings

									$readings[] = $brr;
									//break;

								}

								$sensors['readings'] = $readings;

							}

							$bir[$phenomenon] = $sensors;
						}

						unset($bir['house_id']);
						$boscas[] = $bir;
					}

					$hr['boscas'] = $boscas;

					$r['house'] = $hr;

				}

				unset($r['house_id']);

				$data = $r;

			}
		}
	} else {

		$error[] = "The crop id need to be an interger";

	}

} else {
	$error[] = "Needs an id";

}

$result['result'] = $data;

if (isset($error)) {
	$result['error'] = $error;

}
header('Content-type: application/json');
echo json_encode($result);

$db = NULL;
?>