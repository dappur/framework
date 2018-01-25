-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 25, 2018 at 09:40 PM
-- Server version: 10.1.26-MariaDB
-- PHP Version: 7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `framework`
--

-- --------------------------------------------------------

--
-- Table structure for table `activations`
--

CREATE TABLE `activations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `activations`
--

INSERT INTO `activations` (`id`, `user_id`, `code`, `completed`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'YAE2bTjn8YyE2OVdbP5GwUIOPwn87UHH', 1, '2018-01-25 21:20:10', '2018-01-25 21:20:10', '2018-01-25 21:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Uncategorized', 'uncategorized', 1, '2018-01-25 21:20:12', '2018-01-25 21:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `featured_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video_provider` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publish_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `user_id`, `category_id`, `title`, `description`, `slug`, `content`, `featured_image`, `video_provider`, `video_id`, `publish_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Sample Post - No Featured Media', 'Sample Post - No Featured Media', 'sample-post-no-featured-media', '<div class=\\\"anyipsum-output\\\">\\r\\n<p>Bacon ipsum dolor amet shoulder sausage porchetta frankfurter venison meatloaf kielbasa ham hock. Tail kielbasa bresaola pig pork loin, salami turkey shank cupim fatback. Strip steak short loin picanha pig turducken andouille tail, bresaola sirloin. Meatloaf ham pork chop, prosciutto flank t-bone tongue bresaola drumstick ball tip alcatra burgdoggen. Andouille biltong short loin picanha salami tail. Pork loin shoulder pancetta, kevin beef spare ribs salami. Strip steak salami filet mignon, jowl tail ham biltong venison picanha jerky prosciutto boudin pork belly.</p>\\r\\n<p>Frankfurter beef tri-tip, short ribs pancetta pork belly kielbasa meatball bacon. Flank pork belly short ribs, bresaola corned beef hamburger salami drumstick chicken cupim. Boudin pork chop meatloaf prosciutto biltong, short loin ham hock ball tip jowl shankle corned beef salami sausage. Flank rump bresaola, cupim pork loin strip steak jowl salami landjaeger short ribs corned beef cow.</p>\\r\\n<p>Picanha meatball pancetta short loin leberkas capicola ham hock landjaeger tenderloin jowl. Turkey cow turducken, alcatra fatback shank rump tri-tip pork loin pastrami capicola tail ham sirloin tongue. Ribeye hamburger boudin pork chop tongue pancetta pig turducken sausage. Andouille filet mignon pig tri-tip, fatback cupim ball tip ribeye porchetta flank swine meatloaf kevin. Ground round corned beef boudin pork loin, venison chicken meatloaf ham pork belly alcatra ball tip ham hock picanha porchetta. Pig tri-tip beef ribs shank tongue pork chop cow sirloin porchetta rump kevin sausage short ribs. Ham hock pork chop kevin ground round shank sirloin ham swine filet mignon chicken.</p>\\r\\n<p>Tail pork chop rump short ribs, hamburger prosciutto cow biltong pig tenderloin. Corned beef porchetta rump, turkey buffalo tail tenderloin hamburger alcatra t-bone cupim swine prosciutto pastrami. Pork belly picanha t-bone corned beef pork chop. Shank swine brisket pork. Shankle turkey shoulder andouille. Pork loin ribeye spare ribs bresaola, sirloin porchetta andouille cow. Alcatra jowl boudin cow meatball bresaola kevin frankfurter, pork chop beef ribs capicola ground round filet mignon.</p>\\r\\n<p>Chicken ribeye bacon, short ribs tongue shoulder ground round picanha bresaola. Tenderloin kevin turducken meatball ground round turkey jerky cupim prosciutto biltong flank. Tail cow chicken pork belly. Ham hock rump corned beef meatloaf.</p>\\r\\n</div>\\r\\n<div class=\\\"anyipsum-form-header\\\">Does your lorem ipsum text long for something a little meatier? Give our generator a try&hellip; it&rsquo;s tasty!</div>', NULL, NULL, NULL, '2017-12-06 21:14:00', 1, '2017-12-06 21:14:59', '2018-01-25 09:25:10'),
(2, 1, 1, 'Sample Post - Featured Image', 'Sample Post - Featured Image', 'sample-post-featured-image', '<div class=\\\"anyipsum-output\\\">\\r\\n<p>Bacon ipsum dolor amet shoulder sausage porchetta frankfurter venison meatloaf kielbasa ham hock. Tail kielbasa bresaola pig pork loin, salami turkey shank cupim fatback. Strip steak short loin picanha pig turducken andouille tail, bresaola sirloin. Meatloaf ham pork chop, prosciutto flank t-bone tongue bresaola drumstick ball tip alcatra burgdoggen. Andouille biltong short loin picanha salami tail. Pork loin shoulder pancetta, kevin beef spare ribs salami. Strip steak salami filet mignon, jowl tail ham biltong venison picanha jerky prosciutto boudin pork belly.</p>\\r\\n<p>Frankfurter beef tri-tip, short ribs pancetta pork belly kielbasa meatball bacon. Flank pork belly short ribs, bresaola corned beef hamburger salami drumstick chicken cupim. Boudin pork chop meatloaf prosciutto biltong, short loin ham hock ball tip jowl shankle corned beef salami sausage. Flank rump bresaola, cupim pork loin strip steak jowl salami landjaeger short ribs corned beef cow.</p>\\r\\n<p>Picanha meatball pancetta short loin leberkas capicola ham hock landjaeger tenderloin jowl. Turkey cow turducken, alcatra fatback shank rump tri-tip pork loin pastrami capicola tail ham sirloin tongue. Ribeye hamburger boudin pork chop tongue pancetta pig turducken sausage. Andouille filet mignon pig tri-tip, fatback cupim ball tip ribeye porchetta flank swine meatloaf kevin. Ground round corned beef boudin pork loin, venison chicken meatloaf ham pork belly alcatra ball tip ham hock picanha porchetta. Pig tri-tip beef ribs shank tongue pork chop cow sirloin porchetta rump kevin sausage short ribs. Ham hock pork chop kevin ground round shank sirloin ham swine filet mignon chicken.</p>\\r\\n<p>Tail pork chop rump short ribs, hamburger prosciutto cow biltong pig tenderloin. Corned beef porchetta rump, turkey buffalo tail tenderloin hamburger alcatra t-bone cupim swine prosciutto pastrami. Pork belly picanha t-bone corned beef pork chop. Shank swine brisket pork. Shankle turkey shoulder andouille. Pork loin ribeye spare ribs bresaola, sirloin porchetta andouille cow. Alcatra jowl boudin cow meatball bresaola kevin frankfurter, pork chop beef ribs capicola ground round filet mignon.</p>\\r\\n<p>Chicken ribeye bacon, short ribs tongue shoulder ground round picanha bresaola. Tenderloin kevin turducken meatball ground round turkey jerky cupim prosciutto biltong flank. Tail cow chicken pork belly. Ham hock rump corned beef meatloaf.</p>\\r\\n</div>\\r\\n<div class=\\\"anyipsum-form-header\\\">Does your lorem ipsum text long for something a little meatier? Give our generator a try&hellip; it&rsquo;s tasty!</div>', 'https://baconmockup.com/1200/630', NULL, NULL, '2017-12-06 21:18:00', 1, '2017-12-06 21:19:25', '2018-01-25 09:25:04'),
(3, 1, 1, 'Sample Post - Featured Video', 'Sample Post - Featured Video', 'sample-post-featured-video', '<p>Bacon ipsum dolor amet pancetta short loin picanha drumstick, hamburger beef ribs doner shoulder frankfurter sirloin biltong kielbasa pastrami prosciutto. Boudin cupim burgdoggen, flank ground round shank turkey shankle tail kevin landjaeger. Filet mignon leberkas tongue pig biltong. Venison tri-tip buffalo kielbasa tail leberkas, flank brisket pastrami andouille.</p>\\r\\n<p>Shankle rump ground round, pork burgdoggen bresaola spare ribs bacon pork chop cow sausage. Pastrami pork loin kielbasa frankfurter bacon fatback tri-tip swine turducken sirloin. Meatloaf tongue ball tip beef ribs doner fatback rump hamburger pig corned beef kevin meatball buffalo jerky spare ribs. Meatball biltong beef shoulder alcatra sausage swine pork loin tail chicken. Tongue ham hock flank swine beef ribs porchetta pancetta landjaeger strip steak pork loin fatback jerky meatball spare ribs.</p>\\r\\n<p>Tail strip steak ham jowl kevin doner shoulder pig shank swine drumstick frankfurter. Bacon drumstick pork belly ribeye andouille sausage tri-tip cow fatback. Filet mignon pig jerky strip steak bresaola meatball brisket beef ribs tail burgdoggen sausage tenderloin t-bone. Andouille landjaeger tri-tip, pork chop chicken t-bone boudin. Kevin ball tip boudin t-bone pork. Short loin jerky pork loin chicken buffalo.</p>\\r\\n<p>Burgdoggen capicola sausage pig, frankfurter prosciutto turkey andouille. Pig leberkas short loin tri-tip frankfurter. Landjaeger chuck t-bone, ham kevin strip steak short ribs. Shank flank tail turducken. Meatball jowl pastrami ham hock sirloin kielbasa hamburger. Pig shank bacon pork chop rump fatback.</p>\\r\\n<p>Venison shoulder beef ribs, strip steak t-bone tenderloin ground round brisket shankle pork belly. Jowl sausage shankle chuck, rump short ribs short loin. Prosciutto kevin brisket, andouille short loin sausage cow hamburger pancetta shankle capicola strip steak. Ball tip ground round burgdoggen turducken bacon flank, landjaeger leberkas shank short ribs beef swine cupim jerky biltong. Doner t-bone sirloin picanha. Cow boudin filet mignon salami, leberkas kevin ham hock burgdoggen meatloaf beef drumstick sirloin fatback venison. Tenderloin ham boudin rump frankfurter tail pork chop ground round pig landjaeger pastrami tongue flank tri-tip beef.</p>', NULL, 'youtube', '1bSDtlARvPI', '2017-12-06 22:50:00', 1, '2017-12-06 22:50:53', '2018-01-25 09:23:01');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts_comments`
--

CREATE TABLE `blog_posts_comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blog_posts_comments`
--

INSERT INTO `blog_posts_comments` (`id`, `user_id`, `post_id`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'This is a sample comment.', 1, '2018-01-25 21:20:12', '2018-01-25 21:20:12'),
(2, 1, 1, 'This is a sample pending comment.', 0, '2018-01-25 21:20:12', '2018-01-25 21:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts_replies`
--

CREATE TABLE `blog_posts_replies` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `comment_id` int(10) UNSIGNED NOT NULL,
  `reply` text COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blog_posts_replies`
--

INSERT INTO `blog_posts_replies` (`id`, `user_id`, `comment_id`, `reply`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'This is a sample reply.', 1, '2018-01-25 21:20:12', '2018-01-25 21:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts_tags`
--

CREATE TABLE `blog_posts_tags` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blog_posts_tags`
--

INSERT INTO `blog_posts_tags` (`post_id`, `tag_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2018-01-25 21:20:12', '2018-01-25 21:20:12'),
(2, 1, '2018-01-25 21:20:12', '2018-01-25 21:20:12'),
(3, 1, '2018-01-25 21:20:12', '2018-01-25 21:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `blog_tags`
--

CREATE TABLE `blog_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `blog_tags`
--

INSERT INTO `blog_tags` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sample', 'sample', 1, '2018-01-25 21:20:12', '2018-01-25 21:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED DEFAULT NULL,
  `type_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `group_id`, `type_id`, `name`, `description`, `value`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'timezone', 'Site Timezone', 'America/Los_Angeles', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(2, 1, 2, 'site-name', 'Site Name', 'Dappur', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(3, 1, 2, 'domain', 'Site Domain', 'example.com', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(4, 1, 2, 'support-email', 'Support Email', 'support@example.com', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(5, 1, 2, 'from-email', 'From Email', 'noreply@example.com', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(6, 1, 3, 'theme', 'Site Theme', 'dappur', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(7, 1, 4, 'bootswatch', 'Site Bootswatch', 'cerulean', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(8, 1, 5, 'logo', 'Site Logo', 'https://res.cloudinary.com/dappur/image/upload/c_scale,w_600/v1479072913/site-images/logo-horizontal.png', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(9, 1, 5, 'header-logo', 'Header Logo', 'https://res.cloudinary.com/dappur/image/upload/c_scale,h_75/v1479072913/site-images/logo-horizontal.png', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(10, 2, 3, 'dashboard-theme', 'Dashboard Theme', 'dashboard', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(11, 2, 4, 'dashboard-bootswatch', 'Dashboard Bootswatch', 'slate', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(12, 2, 5, 'dashboard-logo', 'Dashboard Logo', 'https://res.cloudinary.com/dappur/image/upload/c_scale,h_75/v1479072913/site-images/logo-horizontal.png', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(13, 1, 2, 'ga', 'Google Analytics UA', '', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(14, 1, 6, 'activation', 'Activation Required', '1', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(15, 1, 6, 'maintenance-mode', 'Maintenance Mode', '0', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(16, 1, 2, 'privacy-service', 'Privacy Service Statement', 'SERVICE', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(17, 3, 2, 'contact-email', 'Contact Email', 'contact@example.com', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(18, 3, 2, 'contact-phone', 'Contact Phone', '(000) 000-0000', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(19, 3, 2, 'contact-street', 'Contact Street', '123 Harbor Blvd.', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(20, 3, 2, 'contact-city', 'Contact City', 'Oxnard', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(21, 3, 2, 'contact-state', 'Contact State', 'CA', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(22, 3, 2, 'contact-zip', 'Contact Zip', '93035', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(23, 3, 2, 'contact-country', 'Contact Country', 'USA', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(24, 3, 2, 'contact-map-url', 'Map Iframe Url', 'https://goo.gl/oDcRix', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(25, 3, 6, 'contact-map-show', 'Show Map', '1', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(26, 3, 6, 'contact-send-email', 'Send Confirmation Email', '1', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(27, 4, 6, 'blog-enabled', 'Enable Blog', '1', '2018-01-25 21:20:12', '2018-01-25 21:20:12'),
(28, 4, 2, 'blog-per-page', 'Blog Posts Per Page', '2', '2018-01-25 21:20:12', '2018-01-25 21:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `config_groups`
--

CREATE TABLE `config_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `page_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `config_groups`
--

INSERT INTO `config_groups` (`id`, `name`, `description`, `page_name`, `created_at`, `updated_at`) VALUES
(1, 'Site Settings', NULL, NULL, '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(2, 'Dashboard Settings', NULL, NULL, '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(3, 'Contact', 'Contact Page Config', 'contact', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(4, 'Blog', 'Blog Settings', NULL, '2018-01-25 21:20:12', '2018-01-25 21:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `config_types`
--

CREATE TABLE `config_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `config_types`
--

INSERT INTO `config_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'timezone', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(2, 'string', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(3, 'theme', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(4, 'bootswatch', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(5, 'image', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(6, 'boolean', '2018-01-25 21:20:10', '2018-01-25 21:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `contact_requests`
--

CREATE TABLE `contact_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emails`
--

CREATE TABLE `emails` (
  `id` int(10) UNSIGNED NOT NULL,
  `secure_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template_id` int(10) UNSIGNED DEFAULT NULL,
  `send_to` text COLLATE utf8_unicode_ci,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `html` text COLLATE utf8_unicode_ci,
  `plain_text` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emails_drafts`
--

CREATE TABLE `emails_drafts` (
  `id` int(10) UNSIGNED NOT NULL,
  `secure_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `send_to` text COLLATE utf8_unicode_ci,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `html` text COLLATE utf8_unicode_ci,
  `plain_text` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emails_templates`
--

CREATE TABLE `emails_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `html` text COLLATE utf8_unicode_ci,
  `plain_text` text COLLATE utf8_unicode_ci,
  `placeholders` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `emails_templates`
--

INSERT INTO `emails_templates` (`id`, `name`, `slug`, `description`, `subject`, `html`, `plain_text`, `placeholders`, `created_at`, `updated_at`) VALUES
(1, 'Password Reset', 'password-reset', 'Password reset email to user', 'Password Reset Request from {{  settings_site_name  }}', '<h1>{{ settings_site_name }}</h1>\r\n\r\n<p>Hello&nbsp;{{ user_first_name }},</p>\r\n\r\n<p>You are receiving this email because you recently requested a password reset. &nbsp;</p>\r\n\r\n<h3><a href=\"{{ reset_url }}\">Reset Password Now</a></h3>\r\n\r\n<p>If you did not request this reset, then please disregard this email.</p>\r\n\r\n<p>Thank You,</p>\r\n\r\n<p>{{ settings_site_name }}</p>\r\n\r\n', 'Hello {{ user_first_name }},\r\n\r\nYou are receiving this email because you recently requested a password reset.  To continue resetting your password, please click the following link:\r\n\r\n{{ reset_url }}\r\n\r\nIf you did not request this reset, then please disregard this email.\r\n\r\nThank You,\r\n\r\n{{ settings_site_name }}', '[\"reset_url\"]', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(2, 'Registration Complete', 'registration', 'Registration Complete Email', 'Welcome to {{  settings_site_name  }}, {{  user_first_name  }}!', '<h1>{{ settings_site_name }}</h1>\r\n\r\n<p>Hello&nbsp;{{ user_first_name }},</p>\r\n\r\n<p>Welcome to &nbsp;{{ settings_site_name }}. &nbsp;Here are your login details:</p>\r\n\r\n<p>Username:&nbsp;{{ user_username }}<br />\r\nPassword: Chosen at Registration</p>\r\n\r\n<h3><a href=\"https://{{ settings_domain }}\">Visit&nbsp;{{ settings_site_name }}</a></h3>\r\n\r\n<p>Thank You,</p>\r\n\r\n<p>{{ settings_site_name }}</p>', 'Hello {{ user_first_name }},\r\n\r\nWelcome to {{  settings_site_name  }}.  Here are your login details:\r\n\r\nUsername: {{  user_username  }}\r\nPassword: Chosen at Registration\r\n\r\nVisit https://{{  settings_domain  }}\r\n\r\nThank You,\r\n\r\n{{ settings_site_name }}', NULL, '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(3, 'Activation Email', 'activation', 'Account Activation Email', 'Activate Your {{  settings_site_name  }} Account', '<h1>{{ settings_site_name }}</h1>\r\n\r\n<p>Hello&nbsp;{{ user_first_name }},</p>\r\n\r\n<p>Thank you for creating your account. &nbsp;In order to ensure the best possible experience, we require that you verify your email address before you can begin using your account. &nbsp;To do so, simply click the following link and you will be immediately logged in to your account.</p>\r\n\r\n<h3><a href=\"{{ confirm_url }}\">Confirm Email Now</a></h3>\r\n\r\n<p>Thank You,</p>\r\n\r\n<p>{{ settings_site_name }} Team</p>', '{{ settings_site_name }}\r\n\r\nHello {{  user_first_name  }},\r\n\r\nThank you for creating your account.  In order to ensure the best possible experience, we require that you verify your email address before you can begin using your account.  To do so, simply click the following link and you will be immediately logged in to your account.\r\n\r\n{{ confirm_url }}\r\n\r\nThank You,\r\n\r\n{{ settings_site_name }} Team', '[\"confirm_url\"]', '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(4, 'User Contact Confirmation', 'contact-confirmation', 'Contact confirmation sent to the user', 'Contact Confirmation from {{  settings_site_name  }}', '<h1>{{ settings_site_name }}</h1>\r\n\r\n<p>Hello {{ name }},</p>\r\n\r\n<p>We have received your contact request and if it requires a reply, we will be in touch with you soon. &nbsp;here is the information that you submitted:</p>\r\n\r\n<p><strong>Phone:</strong>&nbsp;{{ phone }}<br />\r\n<strong>Comment:</strong>&nbsp;{{ comment }}</p>\r\n\r\n<h3><a href=\"https://{{ settings_domain }}\">Visit {{ settings_site_name }}</a></h3>\r\n\r\n<p>Thank You,</p>\r\n\r\n<p>{{ settings_site_name }} Team</p>', '{{ settings_site_name }}\r\n\r\nHello {{ name }},\r\n\r\nWe have received your contact request and if it requires a reply, we will be in touch with you soon. Here is the information that you submitted:\r\n\r\nName: {{ name }}\r\nPhone: {{ phone }}\r\nComment: {{ comment }}\r\n\r\nVisit https://{{  settings_domain  }}\r\n\r\nThank You,\r\n\r\n{{ settings_site_name }} Team', '[\"name\",\"phone\",\"comment\"]', '2018-01-25 21:20:10', '2018-01-25 21:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `persistences`
--

CREATE TABLE `persistences` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `persistences`
--

INSERT INTO `persistences` (`id`, `user_id`, `code`, `created_at`, `updated_at`) VALUES
(1, 1, '6xQsgjUqqQoijg7uzM5Gmkya2UjUS5cf', '2018-01-25 12:20:51', '2018-01-25 12:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `phinxlog`
--

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `phinxlog`
--

INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20170118012924, 'InitDatabase', '2018-01-26 05:20:08', '2018-01-26 05:20:12', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `permissions` text COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `slug`, `name`, `permissions`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Admin', '{\"user.*\":true,\"email.*\":true,\"settings.*\":true,\"role.*\":true,\"permission.*\":true,\"media.*\":true,\"blog.*\":true,\"dashboard.*\":true}', 1, '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(2, 'developer', 'Developer', '{\"developer.*\":true}', 1, '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(3, 'manager', 'Manager', '{\"user.*\":true,\"user.delete\":false,\"role.*\":true,\"role.delete\":false,\"permission.*\":true,\"permission.delete\":false,\"media.*\":true,\"media.delete\":false,\"blog.*\":true,\"blog.delete\":false,\"dashboard.*\":true}', 1, '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(4, 'user', 'User', '{\"user.account\":true}', 1, '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(5, 'auditor', 'Auditor', '{\"user.view\":true,\"settings.view\":true,\"role.view\":true,\"permission.view\":true,\"blog.view\":true,\"dashboard.view\":true}', 1, '2018-01-25 21:20:10', '2018-01-25 21:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `role_users`
--

CREATE TABLE `role_users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `role_users`
--

INSERT INTO `role_users` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2018-01-25 21:20:10', '2018-01-25 21:20:10'),
(1, 2, '2018-01-25 21:20:10', '2018-01-25 21:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `throttle`
--

CREATE TABLE `throttle` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permissions` text COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `last_name`, `first_name`, `permissions`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin@example.com', 'admin', '$2y$10$sDNVNyEx.2akUdtYMxzZhu61kGHHVSVf2ja.N/w0NgrnagGz1Wvtu', 'User', 'Admin', '', 1, '2018-01-25 12:20:51', '2018-01-25 21:20:10', '2018-01-25 12:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `users_profile`
--

CREATE TABLE `users_profile` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `about` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users_profile`
--

INSERT INTO `users_profile` (`id`, `user_id`, `about`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tail pork chop rump short ribs, hamburger prosciutto cow biltong pig tenderloin. Corned beef porchetta rump, turkey buffalo tail tenderloin hamburger alcatra t-bone cupim swine prosciutto pastrami. Pork belly picanha t-bone corned beef pork chop. Shank swine brisket pork. Shankle turkey shoulder andouille.', '2018-01-25 21:20:12', '2018-01-25 21:20:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activations`
--
ALTER TABLE `activations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activations_user_id_foreign` (`user_id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_categories_name_unique` (`name`),
  ADD UNIQUE KEY `blog_categories_slug_unique` (`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_posts_slug_unique` (`slug`),
  ADD KEY `blog_posts_category_id_foreign` (`category_id`),
  ADD KEY `blog_posts_user_id_foreign` (`user_id`);

--
-- Indexes for table `blog_posts_comments`
--
ALTER TABLE `blog_posts_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_posts_comments_user_id_foreign` (`user_id`),
  ADD KEY `blog_posts_comments_post_id_foreign` (`post_id`);

--
-- Indexes for table `blog_posts_replies`
--
ALTER TABLE `blog_posts_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_posts_replies_user_id_foreign` (`user_id`),
  ADD KEY `blog_posts_replies_comment_id_foreign` (`comment_id`);

--
-- Indexes for table `blog_posts_tags`
--
ALTER TABLE `blog_posts_tags`
  ADD PRIMARY KEY (`post_id`,`tag_id`),
  ADD KEY `blog_posts_tags_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_tags_name_unique` (`name`),
  ADD UNIQUE KEY `blog_tags_slug_unique` (`slug`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_name_unique` (`name`),
  ADD KEY `config_group_id_foreign` (`group_id`),
  ADD KEY `config_type_id_foreign` (`type_id`);

--
-- Indexes for table `config_groups`
--
ALTER TABLE `config_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_groups_name_unique` (`name`),
  ADD UNIQUE KEY `config_groups_page_name_unique` (`page_name`);

--
-- Indexes for table `config_types`
--
ALTER TABLE `config_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_types_name_unique` (`name`);

--
-- Indexes for table `contact_requests`
--
ALTER TABLE `contact_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emails_template_id_foreign` (`template_id`);

--
-- Indexes for table `emails_drafts`
--
ALTER TABLE `emails_drafts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emails_templates`
--
ALTER TABLE `emails_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emails_templates_slug_unique` (`slug`);

--
-- Indexes for table `persistences`
--
ALTER TABLE `persistences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `persistences_code_unique` (`code`),
  ADD KEY `persistences_user_id_foreign` (`user_id`);

--
-- Indexes for table `phinxlog`
--
ALTER TABLE `phinxlog`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reminders_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `role_users`
--
ALTER TABLE `role_users`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_users_role_id_foreign` (`role_id`);

--
-- Indexes for table `throttle`
--
ALTER TABLE `throttle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `throttle_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- Indexes for table `users_profile`
--
ALTER TABLE `users_profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_profile_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activations`
--
ALTER TABLE `activations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blog_posts_comments`
--
ALTER TABLE `blog_posts_comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_posts_replies`
--
ALTER TABLE `blog_posts_replies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_tags`
--
ALTER TABLE `blog_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `config_groups`
--
ALTER TABLE `config_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `config_types`
--
ALTER TABLE `config_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_requests`
--
ALTER TABLE `contact_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emails`
--
ALTER TABLE `emails`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emails_drafts`
--
ALTER TABLE `emails_drafts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emails_templates`
--
ALTER TABLE `emails_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `persistences`
--
ALTER TABLE `persistences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `throttle`
--
ALTER TABLE `throttle`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users_profile`
--
ALTER TABLE `users_profile`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activations`
--
ALTER TABLE `activations`
  ADD CONSTRAINT `activations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `blog_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blog_posts_comments`
--
ALTER TABLE `blog_posts_comments`
  ADD CONSTRAINT `blog_posts_comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_posts_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_posts_replies`
--
ALTER TABLE `blog_posts_replies`
  ADD CONSTRAINT `blog_posts_replies_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `blog_posts_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_posts_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_posts_tags`
--
ALTER TABLE `blog_posts_tags`
  ADD CONSTRAINT `blog_posts_tags_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_posts_tags_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `config`
--
ALTER TABLE `config`
  ADD CONSTRAINT `config_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `config_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `config_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `config_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `emails`
--
ALTER TABLE `emails`
  ADD CONSTRAINT `emails_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `emails_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `persistences`
--
ALTER TABLE `persistences`
  ADD CONSTRAINT `persistences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `reminders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_users`
--
ALTER TABLE `role_users`
  ADD CONSTRAINT `role_users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `throttle`
--
ALTER TABLE `throttle`
  ADD CONSTRAINT `throttle_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users_profile`
--
ALTER TABLE `users_profile`
  ADD CONSTRAINT `users_profile_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
