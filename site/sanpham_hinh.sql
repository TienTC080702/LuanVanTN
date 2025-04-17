-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2017 at 05:57 PM
-- Server version: 10.1.22-MariaDB
-- PHP Version: 7.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perfumeshopdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `sanpham_hinh`
--

CREATE TABLE `sanpham_hinh` (
  `id_hinh` int(11) NOT NULL,
  `idSP` int(11) NOT NULL,
  `urlHinh` varchar(150) NOT NULL,
  `stt` int(11) NOT NULL DEFAULT '1',
  `anhien` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sanpham_hinh`
--

INSERT INTO `sanpham_hinh` (`id_hinh`, `idSP`, `urlHinh`, `stt`, `anhien`) VALUES
(1, 2, 'Calvin Klein Euphoria Blossom Women_2.PNG', 1, 1),
(2, 2, 'Calvin Klein Euphoria Blossom Women_3.PNG', 1, 1),
(3, 2, 'Calvin Klein Euphoria Blossom Women_4.PNG', 1, 1),
(4, 3, 'Calvin Klein Endless Euphoria Women 125ml_2.PNG', 1, 1),
(5, 3, 'Calvin Klein Endless Euphoria Women 125ml_3.PNG', 1, 1),
(6, 3, 'Calvin Klein Endless Euphoria Women 125ml_4.PNG', 1, 1),
(7, 4, 'Chanel Bleu De Chanel Eau De Parfum Men_2.PNG', 1, 1),
(8, 4, 'Chanel Bleu De Chanel Eau De Parfum Men_3.PNG', 1, 1),
(9, 4, 'Chanel Bleu De Chanel Eau De Parfum Men_4.PNG', 1, 1),
(10, 5, 'Chanel Allure Homme Sport Eau Extreme Men_2.PNG', 1, 1),
(11, 5, 'Chanel Allure Homme Sport Eau Extreme Men_3.PNG', 1, 1),
(12, 5, 'Chanel Allure Homme Sport Eau Extreme Men_4.PNG', 1, 1),
(13, 6, 'Dior Sauvage Men_2.PNG', 1, 1),
(14, 6, 'Dior Sauvage Men_3.PNG', 1, 1),
(15, 6, 'Dior Sauvage Men_4.PNG', 1, 1),
(16, 7, 'Dior Eau Sauvage Parfum Men_2.PNG', 1, 1),
(17, 7, 'Dior Eau Sauvage Parfum Men_3.PNG', 1, 1),
(18, 7, 'Dior Eau Sauvage Parfum Men_4.PNG', 1, 1),
(19, 8, 'Dior Eau Sauvage Parfum Men_2.PNG', 1, 1),
(20, 9, 'Diptyque Tam Dao Eau De Parfum_2.PNG', 1, 1),
(21, 9, 'Diptyque Tam Dao Eau De Parfum_3.PNG', 1, 1),
(22, 9, 'Diptyque Tam Dao Eau De Parfum_4.PNG', 1, 1),
(23, 10, 'Elie Saab Le Parfum Women_2.PNG', 1, 1),
(24, 10, 'Elie Saab Le Parfum Women_3.PNG', 1, 1),
(25, 10, 'Elie Saab Le Parfum Women_4.PNG', 1, 1),
(26, 10, 'Elie Saab Le Parfum Women_5.PNG', 1, 1),
(27, 11, 'Elie Saab Rose Couture_2.PNG', 1, 1),
(28, 11, 'Elie Saab Rose Couture_3.PNG', 1, 1),
(29, 11, 'Elie Saab Rose Couture_4.PNG', 1, 1),
(30, 11, 'Elie Saab Rose Couture_5.PNG', 1, 1),
(31, 11, 'Elie Saab Rose Couture_6.PNG', 1, 1),
(32, 12, 'Scuderia Ferrari Racing Red Men_2.PNG', 1, 1),
(33, 12, 'Scuderia Ferrari Racing Red Men_3.PNG', 1, 1),
(34, 13, 'Ferrari Black Men_2.PNG', 1, 1),
(35, 14, 'Ferrari Uomo Men_2.PNG', 1, 1),
(36, 14, 'Ferrari Uomo Men_3.PNG', 1, 1),
(37, 15, 'Armani Prive Vert Malachite For men & women_2.PNG', 1, 1),
(38, 15, 'Armani Prive Vert Malachite For men & women_3.PNG', 1, 1),
(39, 15, 'Armani Prive Vert Malachite For men & women_4.PNG', 1, 1),
(40, 15, 'Armani Prive Vert Malachite For men & women_5.PNG', 1, 1),
(41, 16, 'Armani Prive Rouge Malachite Men & Women_2.PNG', 1, 1),
(42, 16, 'Armani Prive Rouge Malachite Men & Women_3.PNG', 1, 1),
(43, 16, 'Armani Prive Rouge Malachite Men & Women_4.PNG', 1, 1),
(44, 17, 'Armani Prive Rose D\'arabie Intense Unisex_2.PNG', 1, 1),
(45, 17, 'Armani Prive Rose D\'arabie Intense Unisex_3.PNG', 1, 1),
(46, 17, 'Armani Prive Rose D\'arabie Intense Unisex_4.PNG', 1, 1),
(47, 18, 'Givenchy Gentlemen Only Men_2.PNG', 1, 1),
(48, 18, 'Givenchy Gentlemen Only Men_3.PNG', 1, 1),
(49, 18, 'Givenchy Gentlemen Only Men_4.PNG', 1, 1),
(50, 19, 'Givenchy Gentlemen Only Absolute_2.PNG', 1, 1),
(51, 19, 'Givenchy Gentlemen Only Absolute_3.PNG', 1, 1),
(52, 19, 'Givenchy Gentlemen Only Absolute_4.PNG', 1, 1),
(53, 20, 'Gucci Guilty Intense Pour Homme_3.PNG', 1, 1),
(54, 20, 'Gucci Guilty Intense Pour Homme_4.PNG', 1, 1),
(55, 21, 'Gucci Made to Measure Men_2.PNG', 1, 1),
(56, 21, 'Gucci Made to Measure Men_3.PNG', 1, 1),
(57, 21, 'Gucci Made to Measure Men_4.PNG', 1, 1),
(58, 22, 'Gucci Guilty Pour Homme_2.PNG', 1, 1),
(59, 22, 'Gucci Guilty Pour Homme_3.PNG', 1, 1),
(60, 23, 'Gucci Guilty Stud Limited Edition Pour Homme Men_2.PNG', 1, 1),
(61, 23, 'Gucci Guilty Stud Limited Edition Pour Homme Men_3.PNG', 1, 1),
(62, 23, 'Gucci Guilty Stud Limited Edition Pour Homme Men_4.PNG', 1, 1),
(63, 24, 'CaHermes Eau Des Merveilles 4PC_2.PNG', 1, 1),
(64, 25, 'Hermes Terre d`Hermes Gift Set 100ml 3PC_2.PNG', 1, 1),
(65, 25, 'Hermes Terre d`Hermes Gift Set 100ml 3PC_3.PNG', 1, 1),
(66, 25, 'Hermes Terre d`Hermes Gift Set 100ml 3PC_4.PNG', 1, 1),
(67, 25, 'Hermes Terre d`Hermes Gift Set 100ml 3PC_5.PNG', 1, 1),
(68, 26, 'Hermes Terre D’Hermes GiftSet (125ml+30ml)_2.PNG', 1, 1),
(69, 26, 'Hermes Terre D’Hermes GiftSet (125ml+30ml)_3.PNG', 1, 1),
(70, 27, 'Hermes Terre D\'Hermes Giftset 3PC_2.PNG', 1, 1),
(71, 27, 'Hermes Terre D\'Hermes Giftset 3PC_3.PNG', 1, 1),
(72, 27, 'Hermes Terre D\'Hermes Giftset 3PC_4.PNG', 1, 1),
(73, 28, 'Hermes Voyage d\'Hermes Pure Parfum Unisex Gift Set 4PC_2.PNG', 1, 1),
(74, 28, 'Hermes Voyage d\'Hermes Pure Parfum Unisex Gift Set 4PC_3.PNG', 1, 1),
(75, 29, 'Jo Malon Peony & Blush Suede Cologne_3.PNG', 1, 1),
(76, 30, 'Jo Malone Oud & Bergamot Cologne Intense_2.PNG', 1, 1),
(77, 31, 'Mancera Wild Fruits_2.PNG', 1, 1),
(78, 31, 'Mancera Wild Fruits_4.PNG', 1, 1),
(79, 32, 'Rose des Vents Louis Vuitton_2.PNG', 1, 1),
(80, 32, 'Rose des Vents Louis Vuitton_3.PNG', 1, 1),
(81, 32, 'Rose des Vents Louis Vuitton_4.PNG', 1, 1),
(82, 33, 'Mariah Carey M Women_2.PNG', 1, 1),
(83, 33, 'Mariah Carey M Women_3.PNG', 1, 1),
(84, 33, 'Mariah Carey M Women_4.PNG', 1, 1),
(85, 34, 'Prada Candy Florale Giftset_2.PNG', 1, 1),
(86, 35, 'Prada Candy Florale Giftset_3.PNG', 1, 1),
(87, 35, 'Penhaligon\'s Portraits The Tragedy of Lord George_2.PNG', 1, 1),
(88, 35, 'Penhaligon\'s Portraits The Tragedy of Lord George_3.PNG', 1, 1),
(89, 35, 'Penhaligon\'s Portraits The Tragedy of Lord George_4.PNG', 1, 1),
(90, 35, 'Penhaligon\'s Portraits The Tragedy of Lord George_5.PNG', 1, 1),
(91, 36, 'Penhaligon\'s Portraits The Duke_2.PNG', 1, 1),
(92, 36, 'Penhaligon\'s Portraits The Duke_3.PNG', 1, 1),
(93, 36, 'Penhaligon\'s Portraits The Duke_4.PNG', 1, 1),
(94, 36, 'Penhaligon\'s Portraits The Duke_5.PNG', 1, 1),
(95, 37, 'Swarovski Aura Gift Set 3PC_2.PNG', 1, 1),
(96, 37, 'Swarovski Aura Gift Set 3PC_3.PNG', 1, 1),
(97, 37, 'Swarovski Aura Gift Set 3PC_4.PNG', 1, 1),
(98, 38, 'Thierry Mugler Amen Set 3PC_2.PNG', 1, 1),
(99, 38, 'Thierry Mugler Amen Set 3PC_3.PNG', 1, 1),
(100, 39, 'Thierry Mugler Mini Alien & Angel_2.PNG', 1, 1),
(101, 39, 'Thierry Mugler Mini Alien & Angel_3.PNG', 1, 1),
(102, 40, 'Thierry Mugler Angel Set 3x25ml Women_1.PNG', 1, 1),
(103, 41, 'Prada Amber Pour homme Intense.PNG', 103, 1),
(104, 41, 'Prada Amber Pour homme Intense1.PNG', 104, 1),
(105, 41, 'Prada Amber Pour homme Intense2.PNG', 105, 1),
(106, 42, 'Carolina Herrera 212 CH Men Sport1.PNG', 106, 1),
(107, 42, 'Carolina Herrera 212 CH Men Sport2.PNG', 107, 1),
(108, 42, 'Carolina Herrera 212 CH Men Sport3.PNG', 108, 1),
(109, 42, 'Carolina Herrera 212 CH Men Sport4.PNG', 109, 1),
(110, 43, 'Dior Homme Intense1.PNG', 110, 1),
(111, 43, 'Dior Homme Intense2.PNG', 111, 1),
(112, 43, 'Dior Homme Intense3.PNG', 112, 1),
(113, 43, 'Dior Homme Intense4.PNG', 113, 1),
(114, 44, 'Elie Saab Le Parfum Women1.PNG', 114, 1),
(115, 44, 'Elie Saab Le Parfum Women2.PNG', 115, 1),
(116, 44, 'Elie Saab Le Parfum Women3.PNG', 116, 1),
(117, 44, 'Elie Saab Le Parfum Women4.PNG', 117, 1),
(118, 45, 'Byredo Pulp Eau De Parfum_1.PNG', 118, 1),
(119, 45, 'Byredo Pulp Eau De Parfum_2.PNG', 119, 1),
(120, 45, 'Byredo Pulp Eau De Parfum_3.PNG', 120, 1),
(122, 46, 'Byredo Rose Noir_1.PNG', 122, 1),
(121, 45, 'Byredo Pulp Eau De Parfum_4.PNG', 121, 1),
(123, 46, 'Byredo Rose Noir_2.PNG', 123, 1),
(124, 46, 'Byredo Rose Noir_3.PNG', 124, 1),
(125, 46, 'Byredo Rose Noir_4.PNG', 125, 1),
(147, 47, 'Gucci Guilty Tester Women1.PNG', 147, 1),
(148, 47, 'Gucci Guilty Tester Women2.PNG', 148, 1),
(149, 47, 'Gucci Guilty Tester Women3.PNG', 149, 1),
(150, 48, 'Gucci Bamboo Women 75ml1.PNG', 150, 1),
(151, 48, 'Gucci Bamboo Women 75ml2.PNG', 151, 1),
(152, 49, 'Gucci Eau De Parfum II Women1.PNG', 152, 1),
(153, 49, 'Gucci Eau De Parfum II Women2.PNG', 153, 1),
(154, 49, 'Gucci Eau De Parfum II Women3.PNG', 154, 1),
(155, 49, 'Gucci Eau De Parfum II Women4.PNG', 155, 1),
(156, 49, 'Gucci Eau De Parfum II Women5.PNG', 156, 1),
(157, 50, 'Gucci Flora by Gucci Edt Women1.PNG', 157, 1),
(158, 50, 'Gucci Flora by Gucci Edt Women2.PNG', 158, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sanpham_hinh`
--
ALTER TABLE `sanpham_hinh`
  ADD PRIMARY KEY (`id_hinh`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sanpham_hinh`
--
ALTER TABLE `sanpham_hinh`
  MODIFY `id_hinh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1167;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
