/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `annuaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `annuaires` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ENTREPRISE_ID` int DEFAULT NULL,
  `SIEGE_ID` int DEFAULT NULL,
  `SERVICE_CLIENT` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `VIP` int DEFAULT NULL,
  `PRIORITE` int DEFAULT NULL,
  `TOP10` int DEFAULT NULL,
  `CLIENT` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TYPE_CLIENT` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LOGO` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LOGO1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `VIDEO` text COLLATE utf8mb4_unicode_ci,
  `VIDEO_3D` text COLLATE utf8mb4_unicode_ci,
  `SPECIALITE` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TEL_1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TEL_2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CEL_1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CEL_2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FAX` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ADRESSE` text COLLATE utf8mb4_unicode_ci,
  `BP` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EMAIL_1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EMAIL_2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SITE` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `WHATSAPP` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NATURE_ID` int DEFAULT NULL,
  `MAISON_MERE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FACEBOOK` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TWEETER` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `INSTAGRAM` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LINKEDIN` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LOCALISATION` text COLLATE utf8mb4_unicode_ci,
  `GEOLOCALISATION` text COLLATE utf8mb4_unicode_ci,
  `LONGITUDE` float DEFAULT NULL,
  `LATITUDE` float DEFAULT NULL,
  `DESCRIPTION` text COLLATE utf8mb4_unicode_ci,
  `RESUME` varchar(1500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MOT_CLE` text COLLATE utf8mb4_unicode_ci,
  `ACTIVITES` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FORME_JURIDIQUE` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_annuaire_entreprise` (`ENTREPRISE_ID`),
  KEY `pk_annuaires_siege` (`SIEGE_ID`),
  KEY `pk_annuaires_client` (`CLIENT`,`ENTREPRISE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorie_annuaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorie_annuaires` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MOTS_CLES` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LIEN` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DATE_LOG` date DEFAULT NULL,
  `USER` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorie_bonplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorie_bonplan` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION` text COLLATE utf8mb4_unicode_ci,
  `DATE_LOG` date DEFAULT NULL,
  `UTILISATEUR` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorie_immobilier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorie_immobilier` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DATE_LOG` date DEFAULT NULL,
  `USER` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVATION` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorie_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorie_news` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DATE_LOG` date DEFAULT NULL,
  `ACTIVATION` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `communes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LIEN` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `pk_communes_lien` (`LIEN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_entreprises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_entreprises` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entreprise_id` int NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `objet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contactmessage` text COLLATE utf8mb4_unicode_ci,
  `reponse` text COLLATE utf8mb4_unicode_ci,
  `emailEntreprise` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pk_contact_entreprises_entreprise` (`entreprise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objet` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `categorie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `heure` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departements` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ANNUAIRE_ID` int DEFAULT NULL,
  `LIBELLE` varchar(250) DEFAULT NULL,
  `TEL` varchar(50) DEFAULT NULL,
  `CEL` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ent_succusals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ent_succusals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surccursale` int NOT NULL,
  `entreprise_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entreprises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entreprises` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `COMMUNE_ID` int DEFAULT NULL,
  `TYPE_ID` int DEFAULT NULL,
  `TITRE` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SIGLE` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVITE` text COLLATE utf8mb4_unicode_ci,
  `TYPE` int DEFAULT NULL,
  `LIEN` text COLLATE utf8mb4_unicode_ci,
  `PRIORITE` int DEFAULT NULL,
  `COMPTEUR` float DEFAULT '0',
  `VUES_BONUS` int unsigned NOT NULL DEFAULT '0',
  `DATE_LOG` date DEFAULT NULL,
  `DATE_SOUSCRIPTION` date DEFAULT NULL,
  `UTILISATEUR` int DEFAULT NULL,
  `ETAT` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVATION` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_entreprises_id` (`ID`),
  KEY `idx_entreprises_priorite` (`PRIORITE`),
  KEY `pk_entreprises_lien` (`LIEN`(191)),
  KEY `pk_entreprises_type` (`TYPE_ID`),
  KEY `pk_entreprises_commune` (`COMMUNE_ID`),
  KEY `pk_entreprises_utilisateur` (`UTILISATEUR`),
  FULLTEXT KEY `ft_entreprises_recherche` (`TITRE`,`SIGLE`,`ACTIVITE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `historique_vues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historique_vues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `perimetre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operation` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `minimum` int unsigned NOT NULL DEFAULT '0',
  `maximum` int unsigned NOT NULL DEFAULT '0',
  `fiches` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `immobilier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `immobilier` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ENTREPRISE_ID` int DEFAULT NULL,
  `MATRICULE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ILLUSTATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PRIX` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PRIX_M2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CATEGORIE_IMMOBILIER_ID` int DEFAULT NULL,
  `TYPE_IMMOBILIER_ID` int DEFAULT NULL,
  `TYPE_VENDEUR` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LUXE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PERIODE` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EXPIRATION` date DEFAULT NULL,
  `SURFACE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ETAT_BIEN` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `VALIDATION` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PIECES` int DEFAULT NULL,
  `CHAMBRE` int DEFAULT NULL,
  `SALLE_BAIN` int DEFAULT NULL,
  `SALONS` int DEFAULT NULL,
  `ADRESSE` text COLLATE utf8mb4_unicode_ci,
  `LONGITUDE` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LATITUDE` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GOOGLE_MAPS` text COLLATE utf8mb4_unicode_ci,
  `LOCALISATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AGE` int DEFAULT NULL,
  `JARDIN` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PARKING` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PISCINE` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EAU` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ELECTRICITE` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SALLE_GYM` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `STOCKAGE` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `VIDEO_SURVEILLANCE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ASCENSEUR` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `BAIE_VITREE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `BUANDERIE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CHAUFFE_EAU` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GARAGE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GROUPE_ELECTROGENE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GUERITE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `INTERPHONE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MEUBLEE` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SPLIT` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `VIDEO` text COLLATE utf8mb4_unicode_ci,
  `VIDEOS_3D` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FLASH` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION` text COLLATE utf8mb4_unicode_ci,
  `METAS` text COLLATE utf8mb4_unicode_ci,
  `MOTS_CLES` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_NOM` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_TEL` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_CEL` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_EMAIL` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_FACEBOOK` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_WHATSAPP` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_YOUTUBE` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_INSTAGRAM` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `V_TWITTER` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UTILISATEUR` int DEFAULT NULL,
  `DATE_LOG` date DEFAULT NULL,
  `DATE_EXP` date DEFAULT NULL,
  `TYPE` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `pk_immobilier_entreprise` (`ENTREPRISE_ID`),
  KEY `pk_immobilier_type` (`TYPE_IMMOBILIER_ID`),
  KEY `pk_immobilier_categorie` (`CATEGORIE_IMMOBILIER_ID`),
  KEY `pk_immobilier_utilisateur` (`UTILISATEUR`),
  KEY `pk_immobilier_validation_type` (`VALIDATION`,`TYPE_IMMOBILIER_ID`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inscription_annuaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscription_annuaires` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SIGLE` varchar(100) DEFAULT NULL,
  `DENOMINATION` varchar(250) DEFAULT NULL,
  `REGISTE_COMMERCE` varchar(100) DEFAULT NULL,
  `COMPTE_CONTRIBUABLE` varchar(100) DEFAULT NULL,
  `ADRESSE` varchar(250) DEFAULT NULL,
  `VILLE` varchar(100) DEFAULT NULL,
  `COMMUNE` varchar(100) DEFAULT NULL,
  `FIXE` varchar(50) DEFAULT NULL,
  `TELE` varchar(50) DEFAULT NULL,
  `FAX` varchar(50) DEFAULT NULL,
  `EMAIL1` int DEFAULT NULL,
  `EMAIL2` varchar(100) DEFAULT NULL,
  `SITE_WEB` varchar(100) DEFAULT NULL,
  `ANNEE_CREATION` date DEFAULT NULL,
  `FORME_JURIDIQUE` varchar(50) DEFAULT NULL,
  `ACTIVITES` text,
  `RUBRIQUES` text,
  `IMPORTATEUR` varchar(50) DEFAULT NULL,
  `EXPORTATEUR` varchar(50) DEFAULT NULL,
  `CERTIFIER` varchar(50) DEFAULT NULL,
  `FILIATION_CNPS` varchar(50) DEFAULT NULL,
  `CAPITAL` varchar(50) DEFAULT NULL,
  `EFFECTIF` varchar(10) DEFAULT NULL,
  `TYPE_CERTIFICATION` varchar(100) DEFAULT NULL,
  `LATITUDE` varchar(50) DEFAULT NULL,
  `LONGITUDE` varchar(50) DEFAULT NULL,
  `DG_NOM` varchar(100) DEFAULT NULL,
  `DG_FIXE` varchar(50) DEFAULT NULL,
  `DG_CEL` varchar(50) DEFAULT NULL,
  `PDG_NOM` varchar(100) DEFAULT NULL,
  `PDG_FIXE` varchar(50) DEFAULT NULL,
  `PDG_CEL` varchar(50) DEFAULT NULL,
  `GERANT_NOM` varchar(100) DEFAULT NULL,
  `GERANT_FIXE` varchar(50) DEFAULT NULL,
  `GERANT_CEL` varchar(50) DEFAULT NULL,
  `DESCRIPTIONS` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `journal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal` (
  `id_journal` int NOT NULL AUTO_INCREMENT,
  `nom_journal` varchar(70) NOT NULL,
  `slug` varchar(70) NOT NULL,
  `numero_ordre` int NOT NULL,
  `description` text NOT NULL,
  `cle_type` int NOT NULL,
  PRIMARY KEY (`id_journal`),
  KEY `cle_typemedia` (`cle_type`),
  KEY `cle_type` (`cle_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mots_cles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mots_cles` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `LIBELLE` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `natures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `natures` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ENTREPRISE_ID` int DEFAULT NULL,
  `TYPE_NEWS_ID` int DEFAULT NULL,
  `SOURCE_ID` int DEFAULT NULL,
  `SOURCE_WEB_ID` int DEFAULT NULL,
  `CATEGORIE_ID` int DEFAULT NULL,
  `RANG` int DEFAULT NULL,
  `LIEN_WEB` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `VIDEO` text COLLATE utf8mb4_unicode_ci,
  `IMAGE` text COLLATE utf8mb4_unicode_ci,
  `TEXTE` text COLLATE utf8mb4_unicode_ci,
  `RESUME` text COLLATE utf8mb4_unicode_ci,
  `DATE_PARUTION` date DEFAULT NULL,
  `NUMERO_PARUTION` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LIKE` int DEFAULT NULL,
  `NO_LIKE` int DEFAULT NULL,
  `UTILISATEUR` int DEFAULT NULL,
  `DATE_LOG` date DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `pk_news_categorie_date` (`CATEGORIE_ID`,`DATE_PARUTION`),
  KEY `pk_news_entreprise` (`ENTREPRISE_ID`),
  KEY `pk_news_categorie_id` (`CATEGORIE_ID`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `newsletters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletters` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CATEGORIE` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EMAIL` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `client_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_auth_codes` (
  `id` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `client_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_clients` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect_uris` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `grant_types` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_owner_type_owner_id_index` (`owner_type`,`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_device_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_device_codes` (
  `id` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `client_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_code` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `user_approved_at` datetime DEFAULT NULL,
  `last_polled_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oauth_device_codes_user_code_unique` (`user_code`),
  KEY `oauth_device_codes_user_id_index` (`user_id`),
  KEY `oauth_device_codes_client_id_index` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `papier_administratif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `papier_administratif` (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `COMMUNE_ID` int DEFAULT NULL,
  `TITRE` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DESCRIPTION` text COLLATE utf8mb4_unicode_ci,
  `LIEN` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IMAGE` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVE` tinyint(1) NOT NULL DEFAULT '1',
  `ORDRE` int NOT NULL DEFAULT '0',
  `DATE_LOG` date DEFAULT NULL,
  `UTILISATEUR` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `papier_administratif_commune_id_index` (`COMMUNE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `papier_administratif_pieces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `papier_administratif_pieces` (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `PAPIER_ID` bigint unsigned NOT NULL,
  `LIBELLE` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `RANG` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `papier_administratif_pieces_papier_id_index` (`PAPIER_ID`),
  CONSTRAINT `papier_administratif_pieces_papier_id_foreign` FOREIGN KEY (`PAPIER_ID`) REFERENCES `papier_administratif` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parametres_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parametres_site` (
  `cle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valeur` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `periode_garde`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periode_garde` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DEBUT` date DEFAULT NULL,
  `FIN` date DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pharmacie_garde`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pharmacie_garde` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ENTREPRISE_ID` int DEFAULT NULL,
  `DATE_LOG` date DEFAULT NULL,
  `DEBUT_GARDE` date DEFAULT NULL,
  `FIN_GARDE` date DEFAULT NULL,
  `UTILISATEUR` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `pk_pharmacie_garde_entreprise` (`ENTREPRISE_ID`),
  KEY `pk_pharmacie_garde_periode` (`DEBUT_GARDE`,`FIN_GARDE`),
  KEY `pk_pharmacie_garde_fin_debut` (`FIN_GARDE`,`DEBUT_GARDE`,`ENTREPRISE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `photo_annuaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `photo_annuaire` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ANNUAIRE_ID` int DEFAULT NULL,
  `IMAGE` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `pk_photo_annuaire_annuaire` (`ANNUAIRE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `photo_immobilier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `photo_immobilier` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `IMMOBILIER_ID` int DEFAULT NULL,
  `IMAGE` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `pk_photo_immobilier_bien` (`IMMOBILIER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pubs_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pubs_app` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lien` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordre` smallint unsigned NOT NULL DEFAULT '0',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `debut` date DEFAULT NULL,
  `fin` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `quartiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quartiers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ACTIVATION` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `token_admin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `source_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `source_news` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SLUG` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVATION` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `source_news_web`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `source_news_web` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(1500) DEFAULT NULL,
  `LOGO` varchar(100) DEFAULT NULL,
  `DATE_LOG` date DEFAULT NULL,
  `UTILISATEUR` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sous_categorie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sous_categorie` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CATEGORIE_ID` int DEFAULT NULL,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LIEN` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PUBS` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LIEN_PUB` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTIF` text COLLATE utf8mb4_unicode_ci,
  `METAS` text COLLATE utf8mb4_unicode_ci,
  `DATE_LOG` date DEFAULT NULL,
  `UTILISATEUR` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVATION` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `pk_sous_categorie_categorie` (`CATEGORIE_ID`),
  KEY `pk_sous_categorie_lien` (`LIEN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sous_categorie_bonplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sous_categorie_bonplan` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CATEGORIE_ID` int DEFAULT NULL,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION` text COLLATE utf8mb4_unicode_ci,
  `DATE_LOG` date DEFAULT NULL,
  `UTILISATEUR` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `specialite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `specialite` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVATION` int DEFAULT NULL,
  `DESCRIPTION` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DATE_LOG` date DEFAULT NULL,
  `UTILISATEUR` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `statistiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statistiques` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ENTREPRISE_ID` int DEFAULT NULL,
  `TYPE_STAT` int DEFAULT NULL,
  `MOIS` varchar(11) DEFAULT NULL,
  `ANNEE` varchar(11) DEFAULT NULL,
  `STATS` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `pk_statistiques_entreprise` (`ENTREPRISE_ID`,`ANNEE`,`MOIS`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `thematique_annuaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thematique_annuaire` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ENTREPRISE_ID` int DEFAULT NULL,
  `CATEGORIE_ID` int DEFAULT NULL,
  `SOUS_CATEGORIE_ID` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `indx_sous_cat` (`SOUS_CATEGORIE_ID`),
  KEY `idx_thematique_categorie` (`CATEGORIE_ID`),
  KEY `idx_thematique_sous_categorie` (`SOUS_CATEGORIE_ID`),
  KEY `idx_thematique_entreprise` (`ENTREPRISE_ID`),
  KEY `pk_thematique_sous_cat_entreprise` (`SOUS_CATEGORIE_ID`,`ENTREPRISE_ID`),
  KEY `pk_thematique_cat_entreprise` (`CATEGORIE_ID`,`ENTREPRISE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `thematique_bonplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thematique_bonplan` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ENTREPRISE_ID` int DEFAULT NULL,
  `CATEGORIE_ID` int DEFAULT NULL,
  `SOUS_CATEGORIE_ID` int DEFAULT NULL,
  `LOGO` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ILLUSTATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PHOTO_1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PHOTO_2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PRIX` int DEFAULT NULL,
  `RESUME` text COLLATE utf8mb4_unicode_ci,
  `DESCRIPTION` text COLLATE utf8mb4_unicode_ci,
  `TITRE_1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION_1` text COLLATE utf8mb4_unicode_ci,
  `TITRE_2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION_2` text COLLATE utf8mb4_unicode_ci,
  `ADRESSE` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LONGITUDE` float DEFAULT NULL,
  `LATITUDE` float DEFAULT NULL,
  `LOCALISATION` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TEL` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `EMAIL` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PRIORITE` int DEFAULT NULL,
  `TYPE_MENU1` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TYPE_MENU2` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IMAGE_MENU1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IMAGE_MENU2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DATE_MENU2` date DEFAULT NULL,
  `DOC_MENU1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DOC_MENU2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SPECIALITE` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ALERT` int DEFAULT NULL,
  `DATE_FIN` date DEFAULT NULL,
  `VALIDATION` int DEFAULT NULL,
  `USER_ID` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `thems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thems` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TITRE` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_entreprise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_entreprise` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVATION` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_immobilier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_immobilier` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ACTIVATION` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_news` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DESIGNATION` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SLUG` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DESCRIPTION` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ACTIVATION` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `entreprise_id` int DEFAULT NULL,
  `workstation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `CATEGORIE` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_tel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_cel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_facebook` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_twitter` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_instagram` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent_youtube` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` int DEFAULT NULL,
  `titre_menu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_menu` date DEFAULT NULL,
  `user_created` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pk_users_email` (`email`(191)),
  KEY `pk_users_entreprise` (`entreprise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `videoinfos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `videoinfos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lien` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_11_25_000100_add_performance_indexes_to_legacy_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_11_25_093002_create_oauth_auth_codes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_11_25_093003_create_oauth_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_11_25_093004_create_oauth_refresh_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_11_25_093005_create_oauth_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_11_25_093006_create_oauth_device_codes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_11_25_000200_add_search_indexes_to_legacy_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_11_25_000300_add_garde_en_cours_index',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_11_25_000400_convert_legacy_myisam_tables_to_innodb',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_11_25_000500_create_parametres_site_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_11_25_000600_add_vues_bonus_to_entreprises',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_11_25_000700_create_pubs_app_table',7);
