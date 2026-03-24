<?php
defined( 'ABSPATH' ) || exit;

/**
** Library Registry — define all third-party libraries here.
**
** To add a new library:
**   1. Drop its assets into assets/libs/<key>/
**   2. Add an entry below using the same <key>.
**
** Structure per entry:
**   '<key>' => [
**       'handle' => 'surgewpb-<key>',   // wp_enqueue handle prefix
**       'css'    => [ 'assets/libs/<key>/file.css', ... ],
**       'js'     => [ 'assets/libs/<key>/file.js',  ... ],
**   ]
**/
return [

	'aos' => [
		'handle' => 'surgewpb-aos',
		'css'    => [
			'assets/libs/aos/aos.css',
		],
		'js'     => [
			'assets/libs/aos/aos.js',
		],
	],

	'swiper' => [
		'handle' => 'surgewpb-swiper',
		'css'    => [
			'assets/libs/swiper/swiper.css',
		],
		'js'     => [
			'assets/libs/swiper/swiper.js',
		],
	],

];
