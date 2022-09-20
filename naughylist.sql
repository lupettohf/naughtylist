CREATE TABLE `naughtylist` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `protocol` varchar(4) NOT NULL,
  `port` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `count` int(11) NOT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `country` varchar(5) DEFAULT NULL,
  `geo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `naughtylist`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `naughtylist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
COMMIT;