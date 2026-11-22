-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2026 at 02:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pinoydex`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin@pinoydex.com', '$2y$10$mQB0QskGghLLQHcRySzHAORsJE1XC/FfBc7cGAsFSFUho91AZ9m3W', '2026-11-21 23:16:46');

-- --------------------------------------------------------

--
-- Table structure for table `conservation_status`
--

CREATE TABLE `conservation_status` (
  `id` int(11) NOT NULL,
  `status_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conservation_status`
--

INSERT INTO `conservation_status` (`id`, `status_name`) VALUES
(5, 'Critically Endangered'),
(6, 'Data Deficient'),
(4, 'Endangered'),
(1, 'Least Concern'),
(2, 'Near Threatened'),
(7, 'Not Evaluated'),
(3, 'Vulnerable');

-- --------------------------------------------------------

--
-- Table structure for table `family`
--

CREATE TABLE `family` (
  `id` int(11) NOT NULL,
  `family_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `family`
--

INSERT INTO `family` (`id`, `family_name`) VALUES
(1, 'Acanthuridae'),
(2, 'Apogonidae'),
(3, 'Balistidae'),
(4, 'Blenniidae'),
(5, 'Chaetodontidae'),
(6, 'Ephippidae'),
(7, 'Gobiidae'),
(8, 'Holocentridae'),
(9, 'Labridae'),
(10, 'Lutjanidae'),
(11, 'Monacanthidae'),
(12, 'Mullidae'),
(13, 'Nemipteridae'),
(14, 'Pomacanthidae'),
(15, 'Pomacentridae'),
(16, 'Scaridae'),
(17, 'Scorpaenidae'),
(18, 'Serranidae'),
(19, 'Siganidae'),
(20, 'Tetraodontidae');

-- --------------------------------------------------------

--
-- Table structure for table `local_name`
--

CREATE TABLE `local_name` (
  `id` int(11) NOT NULL,
  `species_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `language` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `local_name`
--

INSERT INTO `local_name` (`id`, `species_id`, `name`, `language`) VALUES
(1, 3, 'Papakol', 'Tagalog'),
(2, 3, 'Labahita', 'Tagalog'),
(3, 4, 'Bantay-bothot', 'Visayan'),
(4, 4, 'Mangkot', 'Visayan'),
(5, 15, 'Tarian', 'Tagalog'),
(6, 15, 'Mongit', 'Davawenyo'),
(7, 16, 'Danggit', 'Cebuano/Bisaya'),
(8, 16, 'Borot', 'Dabawenyo'),
(9, 6, 'Paru-paru', 'Tagalog'),
(10, 6, 'Paru-parung dagat', 'Tagalog'),
(11, 13, 'Paru-paru', 'Tagalog'),
(12, 13, 'Paru-parung dagat', 'Tagalog'),
(13, 14, 'Paru-paru', 'Tagalog'),
(14, 14, 'Paru-parung dagat', 'Tagalog'),
(15, 7, 'Danuy ranuy', 'Tagbanwa Calamian'),
(16, 9, 'Balawis', 'Tagalog'),
(17, 11, 'Bungalog', 'Visayan'),
(18, 11, 'Mulmol', 'Tagalog'),
(19, 16, 'Balawis bato', 'Tagalog'),
(20, 22, 'Ganting', 'Visayan'),
(21, 22, 'Suga', 'Bikol');

-- --------------------------------------------------------

--
-- Table structure for table `region`
--

CREATE TABLE `region` (
  `id` int(11) NOT NULL,
  `region_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `region`
--

INSERT INTO `region` (`id`, `region_name`) VALUES
(1, 'Luzon'),
(3, 'Mindanao'),
(4, 'Palawan'),
(5, 'Sulu Archipelago'),
(2, 'Visayas');

-- --------------------------------------------------------

--
-- Table structure for table `species`
--

CREATE TABLE `species` (
  `id` int(11) NOT NULL,
  `scientific_name` varchar(200) NOT NULL,
  `common_name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `venomous_or_toxic` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_endemic` enum('Yes','No') NOT NULL DEFAULT 'No',
  `family_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `image_url` text DEFAULT NULL,
  `image_credit` varchar(500) DEFAULT NULL,
  `source` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `species`
--

INSERT INTO `species` (`id`, `scientific_name`, `common_name`, `description`, `venomous_or_toxic`, `is_endemic`, `family_id`, `status_id`, `image_url`, `image_credit`, `source`) VALUES
(3, 'Acanthurus lineatus', 'Lined Surgeonfish', 'A reef-associated fish with distinctive blue and yellow stripes. It feeds mainly on algae and has a sharp caudal spine.', 'No', 'No', 1, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Blue_lined_Surgeonfish_-_Acanthurus_lineatus.jpg', 'Photo: Bernd | License: CC BY 2.0 | Source: https://commons.wikimedia.org/wiki/File:Blue_lined_Surgeonfish_-_Acanthurus_lineatus.jpg', 'https://www.fishbase.se/summary/Acanthurus-lineatus.html'),
(4, 'Amphiprion clarkii', 'Clarks Anemonefish', 'A reef-associated anemonefish that lives among sea anemones in lagoons and coral reefs.', 'No', 'No', 15, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Amphiprion_clarkii.jpg', 'Photo: Ben Lancaster | License: CC BY-SA 2.5 | Source: https://commons.wikimedia.org/wiki/File:Amphiprion_clarkii.jpg', 'https://www.fishbase.se/summary/Amphiprion-clarkii.html'),
(5, 'Chromis viridis', 'Blue-green Damselfish', 'A small reef fish that forms groups above branching corals and feeds mainly on plankton.', 'No', 'No', 15, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Chromis_viridis_01.jpg', 'Photo: Bjoertvedt | License: CC BY-SA 3.0 | Source: https://commons.wikimedia.org/wiki/File:Chromis_viridis_01.jpg', 'https://www.fishbase.se/summary/Chromis-viridis.html'),
(6, 'Chaetodon auriga', 'Threadfin Butterflyfish', 'A colorful butterflyfish found around coral reefs. It feeds on small invertebrates and other reef organisms.', 'No', 'No', 5, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Chaetodon_auriga.jpg', 'Photo: Philippe Bourjon | License: CC BY-SA 3.0 | Source: https://commons.wikimedia.org/wiki/File:Chaetodon_auriga.jpg', 'https://www.fishbase.se/summary/Chaetodon-auriga.html'),
(7, 'Pterois volitans', 'Red Lionfish', 'A predatory reef fish with distinctive reddish bands and venomous dorsal spines.', 'Yes', 'No', 17, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Pterois_volitans.jpg', 'Photo: Pharaoh Hound | License: CC BY-SA 2.5 | Source: https://commons.wikimedia.org/wiki/File:Pterois_volitans.jpg', 'https://www.fishbase.se/summary/Pterois-volitans.html'),
(8, 'Zebrasoma scopas', 'Brown Scopas Tang', 'The brown scopas tang is a reef-associated surgeonfish with a dark brown body marked by fine pale lines. It inhabits coral-rich lagoons and seaward reefs, where it feeds mainly on algae.', 'No', 'No', 1, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Zebrasoma_scopas.jpg', 'Photo: William Warby | License: CC BY 2.0 | Source: https://commons.wikimedia.org/wiki/File:Zebrasoma_scopas.jpg', 'https://www.fishbase.org/summary/5951'),
(9, 'Siganus vulpinus', 'Foxface Rabbitfish', 'The foxface rabbitfish is recognized by its elongated snout and contrasting yellow, black, and white coloration. It occurs around coral-rich lagoons and seaward reefs. Its fin spines contain venom and can cause painful injuries.', 'Yes', 'No', 19, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Siganus_vulpinus.jpg', 'Photo: Manuel M. | License: CC BY 4.0 | Source: https://commons.wikimedia.org/wiki/File:Siganus_vulpinus.jpg', 'https://www.fishbase.org/summary/4629'),
(10, 'Lutjanus kasmira', 'Bluestripe Snapper', 'The bluestripe snapper is a colorful reef-associated fish with a yellow body and several blue horizontal stripes. It is commonly observed in groups and feeds on smaller marine animals.', 'No', 'No', 10, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Lutjanus_kasmira.jpg', 'Photo: Ryan Oelke | License: CC BY-SA 2.0 | Source: https://commons.wikimedia.org/wiki/File:Lutjanus_kasmira.jpg', 'https://www.fishbase.org/summary/156'),
(11, 'Scarus ghobban', 'Blue-barred Parrotfish', 'The blue-barred parrotfish is a reef fish with distinctive blue and green coloration. It feeds by scraping algae from reef surfaces using its fused teeth, helping contribute to the natural grazing activity of coral reef ecosystems.', 'No', 'No', 16, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Scarus_ghobban.jpg', 'Photo: LASZLO ILYES | License: CC BY 2.0 | Source: https://commons.wikimedia.org/wiki/File:Scarus_ghobban.jpg', 'https://www.fishbase.org/summary/5548'),
(12, 'Ostorhinchus cyanosoma', 'Yellowstriped Cardinalfish', 'The yellowstriped cardinalfish is a small reef-associated fish with a silvery body and several orange-yellow stripes. It shelters beneath coral ledges and in reef crevices, often forming groups, and feeds on planktonic crustaceans and small invertebrates.', 'No', 'No', 2, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Yellowstriped_cardinalfish_%28Ostorhinchus_cyanosoma%29_%2849257412023%29.jpg', 'Photo: Rickard Zerpe | License: CC BY 2.0 | Source: https://commons.wikimedia.org/wiki/File:Yellowstriped_cardinalfish_(Ostorhinchus_cyanosoma)_(49257412023).jpg', 'https://www.fishbase.se/country/CountrySpeciesSummary.php?c_code=608&genusname=Ostorhinchus&speciesname=cyanosoma'),
(13, 'Chaetodon lunula', 'Raccoon Butterflyfish', 'The raccoon butterflyfish is a reef-associated species with a distinctive dark marking across its eyes. It inhabits coral reefs and feeds on a variety of small marine invertebrates.', 'No', 'No', 5, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Chaetodon_lunula.jpg', 'Image: NOAA (uploaded by Haplochromis) | License: Public Domain | Source: https://commons.wikimedia.org/wiki/File:Chaetodon_lunula.jpg', 'https://www.fishbase.se/summary/Chaetodon-lunula'),
(14, 'Chaetodon lineolatus', 'Lined Butterflyfish', 'The lined butterflyfish is a large, colorful reef fish with dark vertical lines across its body. It occurs around coral reefs and feeds on coral polyps and other small reef organisms.', 'No', 'No', 5, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Chaetodon_lineolatus.jpg', 'Photo: Leonard Low | License: CC BY 2.0 | Source: https://commons.wikimedia.org/wiki/File:Chaetodon_lineolatus.jpg', 'https://www.fishbase.se/summary/Chaetodon-lineolatus'),
(15, 'Acanthurus olivaceus', 'Orangespot Surgeonfish', 'The orangespot surgeonfish is recognized by its orange marking near the gill cover. It inhabits coral reefs and rocky areas, feeding mainly on algae and organic material.', 'No', 'No', 1, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Acanthurus_olivaceus_1.jpg', 'Photo: Stan Shebs | License: CC BY-SA 3.0 | Source: https://commons.wikimedia.org/wiki/File:Acanthurus_olivaceus_1.jpg', 'https://www.fishbase.se/summary/acanthurus-olivaceus'),
(16, 'Siganus canaliculatus', 'White-spotted Spinefoot', 'The white-spotted spinefoot is a rabbitfish with small pale spots across its body. It occurs in marine and brackish habitats, including reef-associated areas, and feeds mainly on algae and seagrasses.', 'Yes', 'No', 19, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Siganus_canaliculatus.jpg', 'Photo: Bernard Dupont | License: CC BY-SA 2.0 | Source: https://commons.wikimedia.org/wiki/File:Siganus_canaliculatus.jpg', 'https://www.fishbase.se/summary/Siganus_canaliculatus.html'),
(17, 'Ctenochaetus striatus', 'Striated Surgeonfish', 'The striated surgeonfish has a dark olive-brown body with fine blue lines and orange markings on its head. It inhabits reef flats, lagoons, and seaward reefs, feeding on algae and small invertebrates.', 'Yes', 'No', 1, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Ctenochaetus_striatus.jpg', 'Photo: Derek Keats | License: CC BY 2.0 | Source: https://commons.wikimedia.org/wiki/File:Ctenochaetus_striatus.jpg', 'https://www.fishbase.org/summary/1262'),
(18, 'Amblygobius phalaena', 'Whitebarred Goby', 'The whitebarred goby is a small reef-associated fish with a greenish-brown body and several dark vertical bars. It inhabits sandy and rubble areas near coral reefs and feeds by sifting sand for small organisms.', 'No', 'No', 7, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Amblygobius_phalaena.jpg', 'Photo: Haplochromis | License: CC BY-SA 3.0 | Source: https://commons.wikimedia.org/wiki/File:Amblygobius_phalaena.jpg', 'https://www.fishbase.se/Fieldguide/FieldGuideSummary.php?c_code=608&genusname=Amblygobius&speciesname=phalaena'),
(19, 'Parupeneus barberinus', 'Dash-and-dot Goatfish', 'The dash-and-dot goatfish is a reef-associated fish with distinctive dark markings and paired barbels beneath its chin. It searches sandy and rubble areas near reefs for worms, crustaceans, and other small invertebrates.', 'No', 'No', 12, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Parupeneus_barberinus_296713799.jpg', 'Photo: Storm Martin | License: CC BY 4.0 | Source: https://commons.wikimedia.org/wiki/File:Parupeneus_barberinus_296713799.jpg', 'https://www.fishbase.se/Fieldguide/FieldGuideSummary.php?c_code=608&genusname=Parupeneus&speciesname=barberinus'),
(20, 'Cephalopholis argus', 'Peacock Hind', 'The peacock hind is a grouper commonly found in shallow coral reef habitats. Its brown body is covered with small blue spots. It feeds mainly on smaller fishes and crustaceans. Some individuals may contain ciguatoxins.', 'Yes', 'No', 18, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Cephalopholis_argus_688994084.jpg', 'Photo: Roger Rittmaster | License: CC BY 4.0 | Source: https://commons.wikimedia.org/wiki/File:Cephalopholis_argus_688994084.jpg', 'https://www.fishbase.se/Fieldguide/FieldGuideSummary.php?c_code=608&genusname=Cephalopholis&speciesname=argus'),
(21, 'Centropyge vrolikii', 'Pearlscale Angelfish', 'The pearlscale angelfish is a small reef-associated angelfish with pale coloration on the front portion of its body and darker coloration toward the rear. It inhabits coral-rich lagoons and seaward reefs and feeds mainly on algae.', 'No', 'No', 14, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Centropyge_vrolikii.jpg', 'Photo: Izuzuki diver | License: CC BY-SA 2.5 | Source: https://commons.wikimedia.org/wiki/File:Centropyge_vrolikii.jpg', 'https://www.fishbase.se/Fieldguide/FieldGuideSummary.php?c_code=608&genusname=Centropyge&speciesname=vrolikii'),
(22, 'Sargocentron rubrum', 'Redcoat Squirrelfish', 'The redcoat squirrelfish has a reddish body with silvery-white horizontal stripes. It inhabits coastal reefs, lagoons, and rocky crevices, where it often shelters during the day. Its spines can cause venomous injuries.', 'Yes', 'No', 8, 1, 'https://commons.wikimedia.org/wiki/Special:FilePath/Sargocentron_rubrum.jpg', 'Photo: Matthieu Sontag | License: CC BY-SA 3.0 | Source: https://commons.wikimedia.org/wiki/File:Sargocentron_rubrum.jpg', 'https://www.fishbase.org/summary/6625'),
(24, 'Altrichthys alelia', 'Alelia\'s Damselfish', 'A small damselfish native to the Philippines that inhabits coral reef environments. It is associated with extensive growths of living corals, particularly Porites cylindrica. Adults exhibit parental care by protecting their developing offspring.', 'No', 'Yes', 15, 4, 'https://commons.wikimedia.org/wiki/Special:FilePath/Altrichthys_alelia_%2810.3897-zookeys.675.12061%29_Figure_3.jpg', 'Bernardi G., Longo G.C. and Quiros T.E.A.L. (2017), CC BY 4.0', 'https://www.fishbase.se/summary/Altrichthys-alelia');

-- --------------------------------------------------------

--
-- Table structure for table `species_region`
--

CREATE TABLE `species_region` (
  `species_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `species_region`
--

INSERT INTO `species_region` (`species_id`, `region_id`) VALUES
(3, 4),
(4, 2),
(5, 2),
(5, 4),
(6, 4),
(7, 4),
(8, 2),
(9, 2),
(10, 4),
(11, 2),
(12, 4),
(13, 2),
(14, 4),
(15, 4),
(16, 3),
(16, 4),
(17, 2),
(17, 4),
(18, 2),
(18, 3),
(19, 2),
(20, 2),
(21, 2),
(22, 1),
(22, 2),
(22, 3),
(22, 4),
(24, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `conservation_status`
--
ALTER TABLE `conservation_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `family`
--
ALTER TABLE `family`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `family_name` (`family_name`);

--
-- Indexes for table `local_name`
--
ALTER TABLE `local_name`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_local_name_species` (`species_id`);

--
-- Indexes for table `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `region_name` (`region_name`);

--
-- Indexes for table `species`
--
ALTER TABLE `species`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_species_family` (`family_id`),
  ADD KEY `fk_species_status` (`status_id`);

--
-- Indexes for table `species_region`
--
ALTER TABLE `species_region`
  ADD PRIMARY KEY (`species_id`,`region_id`),
  ADD KEY `fk_species_region_region` (`region_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `conservation_status`
--
ALTER TABLE `conservation_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `family`
--
ALTER TABLE `family`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `local_name`
--
ALTER TABLE `local_name`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `region`
--
ALTER TABLE `region`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `species`
--
ALTER TABLE `species`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `local_name`
--
ALTER TABLE `local_name`
  ADD CONSTRAINT `fk_local_name_species` FOREIGN KEY (`species_id`) REFERENCES `species` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `species`
--
ALTER TABLE `species`
  ADD CONSTRAINT `fk_species_family` FOREIGN KEY (`family_id`) REFERENCES `family` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_species_status` FOREIGN KEY (`status_id`) REFERENCES `conservation_status` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `species_region`
--
ALTER TABLE `species_region`
  ADD CONSTRAINT `fk_species_region_region` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_species_region_species` FOREIGN KEY (`species_id`) REFERENCES `species` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
