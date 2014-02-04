<?php

/**
 * Class MemberRepository
 *
 * @author Peter Grassberger aka. PeterTheOne <petertheone@piratenpartei.at>
 */
Class MemberRepository {

    private $pdo;

    /**
     *
     */
    public function __construct() {
        include '../adm_api/config.php';
        $this->pdo = new PDO('mysql:host=' . $g_adm_srv . ';dbname=' . $g_adm_db, $g_adm_usr, $g_adm_pw);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @return array
     */
    public function getMemberCount() {
        $statement = $this->pdo->prepare('
            SELECT
                timestamp AS timestamp,
                users AS registeredMembers,
                members AS payingMembers,
                akk AS payingAndVerifiedMembers
            FROM
                ppoe_mv_info.mv_statistik
            WHERE
                LO = 0
            ORDER BY
                timestamp ASC;
        ');
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_OBJ);
        return $statement->fetchAll();
    }

    /**
     * @param $stateOrganisation
     * @return array
     * @throws Exception
     */
    public function getMemberCountByStateOrganisation($stateOrganisation) {
        // validate Input
        if (!is_numeric($stateOrganisation)) {
            throw new Exception('"stateOrganisation" is not numeric.');
        }
        $stateOrganisation = intval($stateOrganisation);
        if ($stateOrganisation < 0 || $stateOrganisation > 9) {
            throw new Exception('"stateOrganisation" is not in the range of 0 to 9.');
        }

        if ($stateOrganisation === 0) {
            return $this->getMemberCount();
        }

        // correct stateOrganisation IDs
        $states = array(
            1 => 38, // Burgenland
            2 => 40, // Carinthia
            3 => 39, // Lower Austria
            4 => 41, // Upper Austria
            5 => 42, // Salzburg
            6 => 43, // Styria
            7 => 44, // Tyrol
            8 => 45, // Vorarlberg
            9 => 37, // Vienna
            10 => 10 // No StateOrganisation
        );
        $stateOrganisation = $states[$stateOrganisation];

        $statement = $this->pdo->prepare('
            SELECT
                timestamp,
                users AS registeredMembers,
                members AS payingMembers,
                akk AS payingAndVerifiedMembers
            FROM
                ppoe_mv_info.mv_statistik
            WHERE
                LO = :stateOrganisation
            ORDER BY
              timestamp ASC;
        ');
        $statement->bindParam(':stateOrganisation', $stateOrganisation, PDO::PARAM_INT);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_OBJ);
        return $statement->fetchAll();
    }
}