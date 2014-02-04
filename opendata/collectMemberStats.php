<?php
/**
 * This file should be moved to adm_api
 */
include 'config.php';
//include '../adm_api/config.php';

define('STATS_DATABASE', 'ppoe_mv_info');
define('NUTS_TABLE', 'mv_nuts_stats');

function writeMessage($text) {
    echo date_format(new DateTime('now'), 'Y-m-d H:i:s') . ': ' . $text . '\n';
}

/**
 * @param PDO $pdo
 * @param $database
 * @param $table
 * @return bool
 */
function tableExists(PDO $pdo, $database, $table) {
    $statement = $pdo->prepare('SELECT * FROM information_schema.tables WHERE table_schema = :database AND table_name = :table LIMIT 1;');
    $statement->bindParam(':database', $database);
    $statement->bindParam(':table', $table);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_OBJ);
    $result = $statement->fetch();
    return !empty($result);
}

function isThereAnEntryOfToday(PDO $pdo, $database, $table) {
    $date = new DateTime('now');
    $startOfDay = date_format($date, 'Y-m-d 00:00:00');
    $endOfDay = date_format($date, 'Y-m-d 23:59:59');
    $statement = $pdo->prepare('SELECT * FROM ' . $database . '.' . $table . ' WHERE timestamp >= :startOfDay AND timestamp <= :endOfDay LIMIT 1;');
    $statement->bindParam(':startOfDay', $startOfDay);
    $statement->bindParam(':endOfDay', $endOfDay);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_OBJ);
    $result = $statement->fetch();
    return !empty($result);
}

/**
 * @param PDO $pdo
 * @return bool
 */
function createNutsTable(PDO $pdo) {
    $statement = $pdo->prepare('
        CREATE TABLE IF NOT EXISTS
            ' . STATS_DATABASE . '.' . NUTS_TABLE . '
            (
                timestamp timestamp,
                nutsLevel INT NOT NULL,
                nutsId TEXT NOT NULL,
                registeredMembers INT,
                payingMembers INT,
                payingAndVerifiedMembers INT
            )
    ');
    $statement->execute();
    return true;
}

/**
 * @param PDO $pdo
 */
function createNutsEntries(PDO $pdo) {
    $statement = $pdo->prepare('SELECT * FROM ppoe_mitglieder.nutsdata;');
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_OBJ);
    $nutsList = $statement->fetchALL();

    $date = date_format(new DateTime('now'), 'Y-m-d H:i:s');
    foreach ($nutsList as $nuts) {
        $statement = $pdo->prepare('
            SELECT COUNT(*) FROM (
                SELECT usr_id FROM ppoe_api_data.members
                    LEFT JOIN adm_user_data ON usd_usr_id = usr_id AND usd_usf_id IN (4,39)
                    LEFT JOIN nutsplz ON usd_value = plz
                WHERE nuts = :nutsId GROUP BY usr_id
            ) A;
        ');
        $statement->bindParam(':nutsId', $nuts->id);
        $statement->execute();
        $registeredMembers = $statement->fetchColumn(0);

        $statement = $pdo->prepare('
            SELECT COUNT(*) FROM (
                SELECT usr_id FROM ppoe_api_data.members
                    LEFT JOIN adm_user_data ON usd_usr_id = usr_id AND usd_usf_id IN (4,39)
                    LEFT JOIN nutsplz ON usd_value = plz
                WHERE nuts = :nutsId AND MB = 1 GROUP BY usr_id
            ) A;
        ');
        $statement->bindParam(':nutsId', $nuts->id);
        $statement->execute();
        $payingMembers = $statement->fetchColumn(0);

        $statement = $pdo->prepare('
            SELECT COUNT(*) FROM (
                SELECT usr_id FROM ppoe_api_data.members
                    LEFT JOIN adm_user_data ON usd_usr_id = usr_id AND usd_usf_id IN (4,39)
                    LEFT JOIN nutsplz ON usd_value = plz
                WHERE nuts = :nutsId AND MB = 1 AND Akk = 1 GROUP BY usr_id
            ) A;
        ');
        $statement->bindParam(':nutsId', $nuts->id);
        $statement->execute();
        $payingAndVerifiedMembers = $statement->fetchColumn(0);

        // todo: also make nutsLevels 1 and 2 available
        $nutsLevel = 3;
        $statement = $pdo->prepare('
            INSERT INTO ppoe_mv_info.mv_nuts_stats (
                timestamp,
                nutsLevel,
                nutsId,
                registeredMembers,
                payingMembers,
                payingAndVerifiedMembers
            ) VALUES (
                :timestamp,
                :nutsLevel,
                :nutsId,
                :registeredMembers,
                :payingMembers,
                :payingAndVerifiedMembers
            );
        ');
        $statement->bindParam(':timestamp', $date);
        $statement->bindParam(':nutsLevel', $nutsLevel);
        $statement->bindParam(':nutsId', $nuts->id);
        $statement->bindParam(':registeredMembers', $registeredMembers);
        $statement->bindParam(':payingMembers', $payingMembers);
        $statement->bindParam(':payingAndVerifiedMembers', $payingAndVerifiedMembers);
        $statement->execute();
    }
}

try {
    $pdo = new PDO('mysql:host=' . $g_adm_srv . ';dbname=' . $g_adm_db, $g_adm_usr, $g_adm_pw);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!tableExists($pdo, STATS_DATABASE, NUTS_TABLE)) {
        createNutsTable($pdo);
        writeMessage('nuts table created.');
    }
    //if (!isThereAnEntryOfToday($pdo, STATS_DATABASE, NUTS_TABLE)) {
        createNutsEntries($pdo);
        writeMessage('nuts entries created.');
    //}

} catch (Exception $exception) {
    writeMessage($exception->getMessage());
}