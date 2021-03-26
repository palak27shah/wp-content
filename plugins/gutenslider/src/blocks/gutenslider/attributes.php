<?php

$gutenslider_attributes = array(
	'gsBlockId'           => array(
		'type'    => 'string',
	),
	'align'             => array(
		'type'    => 'string',
		'default' => 'none',
	),
	'contentMode'       => array(
		'type'    => 'string',
		'default' => 'change',
	),
	'bgImageId'         => array(
		'type' => 'number',
	),
	'fadeMode'          => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'slidesToShow'      => array(
		'type'    => 'number',
		'default' => 1,
	),
	'slidesToShowMd'    => array(
		'type'    => 'number',
		'default' => 1,
	),
	'slidesToShowSm'    => array(
		'type'    => 'number',
		'default' => 1,
	),
	'slidesToScroll'    => array(
		'type'    => 'number',
		'default' => 1,
	),
	'slidesToScrollMd'  => array(
		'type'    => 'number',
		'default' => 1,
	),
	'slidesToScrollSm'  => array(
		'type'    => 'number',
		'default' => 1,
	),
	'sliderHeight'      => array(
		'type'    => 'string',
		'default' => '50vh',
	),
	'sliderHeightMd'    => array(
		'type'    => 'string',
		'default' => '50vh',
	),
	'sliderHeightSm'    => array(
		'type'    => 'string',
		'default' => '50vh',
	),
	'isFullScreen'      => array(
		'type'    => 'boolean',
		'default' => false,
	),
	'pauseOnHover'      => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'pauseOnFocus'      => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'pauseOnDotsHover'  => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'enableSpacing'     => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'paddingX'          => array(
		'type'    => 'string',
		'default' => '40px',
	),
	'paddingXMd'        => array(
		'type'    => 'string',
		'default' => '40px',
	),
	'paddingXSm'        => array(
		'type'    => 'string',
		'default' => '20px',
	),
	'paddingY'          => array(
		'type'    => 'string',
		'default' => '40px',
	),
	'paddingYMd'        => array(
		'type'    => 'string',
		'default' => '40px',
	),
	'paddingYSm'        => array(
		'type'    => 'string',
		'default' => '20px',
	),
	'spacingX'          => array(
		'type'    => 'number',
		'default' => 40,
	),
	'spacingY'          => array(
		'type'    => 'number',
		'default' => 40,
	),
	'spacingXMobile'    => array(
		'type'    => 'number',
		'default' => 40,
	),
	'spacingYMobile'    => array(
		'type'    => 'number',
		'default' => 40,
	),
	'spacingXTablet'    => array(
		'type'    => 'number',
		'default' => 40,
	),
	'spacingYTablet'    => array(
		'type'    => 'number',
		'default' => 40,
	),
	'spacingXDesktop'   => array(
		'type'    => 'number',
		'default' => 40,
	),
	'spacingYDesktop'   => array(
		'type'    => 'number',
		'default' => 40,
	),
	'spacingXUnit'      => array(
		'type'    => 'string',
		'default' => 'px',
	),
	'spacingYUnit'      => array(
		'type'    => 'string',
		'default' => 'px',
	),
	'autoplay'          => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'duration'          => array(
		'type'    => 'number',
		'default' => 3,
	),
	'fadeSpeed'         => array(
		'type'    => 'number',
		'default' => 0.7,
	),
	'arrows'            => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'arrowsMd'          => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'arrowsSm'          => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'arrowSize'         => array(
		'type'    => 'number',
		'default' => 30,
	),
	'arrowSizeMd'         => array(
		'type'    => 'number',
		'default' => 30,
	),
	'arrowSizeSm'         => array(
		'type'    => 'number',
		'default' => 30,
	),
	'arrowColor'        => array(
		'type'    => 'string',
		'default' => '#ffffff',
	),
	'arrowStyle'        => array(
		'type'    => 'string',
		'default' => 'arrow-style-1',
	),
	'arrowPosition'     => array(
		'type' 		=> 'string',
		'default' => 'arrow-position-sides',
	),
	'arrowMixBlendMode' => array(
		'type'    => 'string',
		'default' => 'none',
	),
	'arrowBgColor'      => array(
		'type' 		=> 'string',
		'default' => 'transparent',
	),
	'arrowHoverColor'   => array(
		'type'    => 'string',
		'default' => '#eeeeee',
	),
	'arrowBgHoverColor' => array(
		'type' 		=> 'string',
		'default' => 'transparent',
	),
	'arrowXOffset'      => array(
		'type'      => 'number',
		'default'   => 10,
	),
	'arrowXOffsetMd'    => array(
		'type'      => 'number',
		'default'   => 10,
	),
	'arrowXOffsetSm'    => array(
		'type'      => 'number',
		'default'   => 10,
	),
	'arrowYOffset'      => array(
		'type'      => 'number',
		'default'   => 0,
	),
	'arrowYOffsetMd'    => array(
		'type'      => 'number',
		'default'   => 0,
	),
	'arrowYOffsetSm'    => array(
		'type'      => 'number',
		'default'   => 0,
	),
	'dots'              => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'dotsMd'            => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'dotsSm'            => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'dotSize'           => array(
		'type'    => 'number',
		'default' => 25,
	),
	'dotSizeMd'         => array(
		'type'    => 'number',
		'default' => 25,
	),
	'dotSizeSm'         => array(
		'type'    => 'number',
		'default' => 25,
	),
	'dotColor'          => array(
		'type'    => 'string',
		'default' => '#ffffff',
	),
	'dotStyle'          => array(
		'type'    => 'string',
		'default' => 'dot-style-1',
	),
	'hasParallax'       => array(
		'type'    => 'boolean',
		'default' => false,
	),
	'parallaxDirection' => array(
		'type'    => 'string',
		'default' => 'down',
	),
	'parallaxAmount'    => array(
		'type'    => 'number',
		'default' => 1.3,
	),
	'overlayColor'      => array(
		'type' => 'string',
	),
	'dimRatio'          => array(
		'type'    => 'number',
		'default' => 0.5,
	),
	'rgbaBackground'    => array(
		'type'    => 'string',
		'default' => '',
	),
	'textColor'         => array(
		'type' => 'string',
	),
	'mixBlendMode'      => array(
		'type'    => 'string',
		'default' => 'mb-none',
	),
	'contentOpacity'    => array(
		'type'    => 'number',
		'default' => 1,
	),
	'verticalAlign'     => array(
		'type'    => 'string',
		'default' => 'center',
	),
	'loop'              => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'visibleOnDesktop'  => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'visibleOnTablet'   => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'visibleOnMobile'   => array(
		'type'    => 'boolean',
		'default' => true,
	),
	'adaptiveHeight'   => array(
		'type'    => 'boolean',
		'default' => false,
	),
	'contentPosition'   => array(
		'type'    => 'string',
		'default' => 'center',
	),
	'arrowXSpacing'     => array(
		'type'      => 'number',
		'default'   => 0,
	),
);

$eedee_gutenslider_arrows = array(
	'arrow-style-1' => array(
		'next' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><circle class="bg" cx="11" cy="12" r="11" /><path fill="none" d="M0 0h24v24H0z"/><path class="arrow" d="M13.172 12l-4.95-4.95 1.414-1.414L16 12l-6.364 6.364-1.414-1.414z"/></svg>',
		'prev'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><circle class="bg" cx="13" cy="12" r="11" /><path fill="none" d="M0 0h24v24H0z"/><path class="arrow" d="M10.828 12l4.95 4.95-1.414 1.414L8 12l6.364-6.364 1.414 1.414z"/></svg>',
	),
	'arrow-style-2' => array(
		'next' => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" x="0px" y="0px" viewBox="0 0 45 45"><circle class="bg" cx="22.5" cy="22.5" r="22.5" /><path class="arrow" transform="translate(6.755, 6.755)" d="M21.205,5.007c-0.429-0.444-1.143-0.444-1.587,0c-0.429,0.429-0.429,1.143,0,1.571l8.047,8.047H1.111 C0.492,14.626,0,15.118,0,15.737c0,0.619,0.492,1.127,1.111,1.127h26.554l-8.047,8.032c-0.429,0.444-0.429,1.159,0,1.587 c0.444,0.444,1.159,0.444,1.587,0l9.952-9.952c0.444-0.429,0.444-1.143,0-1.571L21.205,5.007z"/></svg>',
		'prev'  => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" x="0px" y="0px"
			 viewBox="0 0 45 45" xml:space="preserve">
		<circle class="bg" cx="22.5" cy="22.5" r="22.5" /><path class="arrow" transform="translate(6.755, 6.755)" d="M10.273,5.009c0.444-0.444,1.143-0.444,1.587,0c0.429,0.429,0.429,1.143,0,1.571l-8.047,8.047h26.554
			c0.619,0,1.127,0.492,1.127,1.111c0,0.619-0.508,1.127-1.127,1.127H3.813l8.047,8.032c0.429,0.444,0.429,1.159,0,1.587
			c-0.444,0.444-1.143,0.444-1.587,0l-9.952-9.952c-0.429-0.429-0.429-1.143,0-1.571L10.273,5.009z"/>
		</svg>',
	),
	'arrow-style-3' => array(
		'next' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><circle class="bg" cx="12" cy="12" r="10" /><path class="" fill="none" d="M0 0h24v24H0z"/><path class="arrow" stroke-width="0px" d="M12 11V8l4 4-4 4v-3H8v-2h4zm0-9c5.52 0 10 4.48 10 10s-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2zm0 18c4.42 0 8-3.58 8-8s-3.58-8-8-8-8 3.58-8 8 3.58 8 8 8z"/></svg>',
		'prev'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><circle class="bg" cx="12" cy="12" r="10" /><path class="" fill="none" d="M0 0h24v24H0z"/><path class="arrow" stroke-width="0px" d="M12 2c5.52 0 10 4.48 10 10s-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2zm0 18c4.42 0 8-3.58 8-8s-3.58-8-8-8-8 3.58-8 8 3.58 8 8 8zm0-9h4v2h-4v3l-4-4 4-4v3z"/></svg>',
	),
	'arrow-style-4' => array(
		'next' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 514 514"><circle class="bg" cx="257" cy="257" r="257" /><path class="arrow" d="M471.7,242.5l-416-192A16,16,0,0,0,36.2,74.6L173,257,36.2,439.4a16,16,0,0,0,19.5,24.1l416-192a16,16,0,0,0,0-29Z"/></svg>',
		'prev' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 514 514"><circle class="bg" cx="257" cy="257" r="257" /><path class="arrow" d="M42.28,271.57l416,192a16,16,0,0,0,19.5-24.1L341,257.07l136.8-182.4a16,16,0,0,0-19.5-24.1l-416,192a16,16,0,0,0,0,29Z"/></svg>',
	),
	'arrow-style-5' => array(
		'next' => '<svg width="24px" height="24px" viewBox="0 0 590 590"><circle class="bg" cx="295" cy="295" r="295" /><path class="arrow" d="M548.16,283.6l-96-112A16,16,0,0,0,424,182v49.89L54,278.13a16,16,0,0,0,0,31.75l370,46.23V406a16,16,0,0,0,28.16,10.4l96-112A16,16,0,0,0,548.16,283.6Z"/></svg>',
		'prev' => '<svg width="24px" height="24px" viewBox="0 0 590 590"><circle class="bg" cx="295" cy="295" r="295" /><path class="arrow" d="M43.84,304.4l96,112A16,16,0,0,0,168,406V356.11l370-46.24a16,16,0,0,0,0-31.75L168,231.88V182a16,16,0,0,0-28.16-10.4l-96,112A16,16,0,0,0,43.84,304.4Z"/></svg>',
	),
	'arrow-style-6' => array(
		'prev' => '<svg width="24" height="24" viewBox="0 0 128 128" width="24" xmlns="http://www.w3.org/2000/svg"><circle class="bg" cx="64" cy="64" r="64" /><path stroke-width="0" class="arrow" d="m76 100c-1.023 0-2.047-.391-2.828-1.172l-32-32c-1.563-1.563-1.563-4.094 0-5.656l32-32c1.563-1.563 4.094-1.563 5.656 0s1.563 4.094 0 5.656l-29.172 29.172 29.172 29.172c1.563 1.563 1.563 4.094 0 5.656-.781.781-1.805 1.172-2.828 1.172zm52-36c0-35.289-28.711-64-64-64s-64 28.711-64 64 28.711 64 64 64 64-28.711 64-64zm-8 0c0 30.879-25.121 56-56 56s-56-25.121-56-56 25.121-56 56-56 56 25.121 56 56z"/></svg>',
		'next' => '<svg id="Layer_1" height="24" viewBox="0 0 128 128" width="24" xmlns="http://www.w3.org/2000/svg"><circle class="bg" cx="64" cy="64" r="64" /><path stroke-width="0" class="arrow" d="m52 100c-1.023 0-2.047-.391-2.828-1.172-1.563-1.563-1.563-4.094 0-5.656l29.172-29.172-29.172-29.172c-1.563-1.563-1.563-4.094 0-5.656s4.094-1.563 5.656 0l32 32c1.563 1.563 1.563 4.094 0 5.656l-32 32c-.781.781-1.805 1.172-2.828 1.172zm76-36c0-35.289-28.711-64-64-64s-64 28.711-64 64 28.711 64 64 64 64-28.711 64-64zm-8 0c0 30.879-25.121 56-56 56s-56-25.121-56-56 25.121-56 56-56 56 25.121 56 56z"/></svg>',
	),
	'arrow-style-7' => array(
		'prev' => '<svg height="512" viewBox="0 0 128 128" width="512" xmlns="http://www.w3.org/2000/svg"><rect class="bg" x="5" y="5" width="115" height="115" rx="18" ry="18" /><path class="arrow" d="m104 124h-80c-11.027 0-20-8.973-20-20v-80c0-11.027 8.973-20 20-20h80c11.027 0 20 8.973 20 20v80c0 11.027-8.973 20-20 20zm-80-112c-6.617 0-12 5.383-12 12v80c0 6.617 5.383 12 12 12h80c6.617 0 12-5.383 12-12v-80c0-6.617-5.383-12-12-12zm40 92c-.969 0-1.926-.354-2.676-1.027l-40-36c-.844-.758-1.324-1.838-1.324-2.973s.48-2.215 1.324-2.973l40-36c1.176-1.057 2.863-1.32 4.305-.682 1.441.643 2.371 2.075 2.371 3.655v20h32c2.211 0 4 1.791 4 4v24c0 2.209-1.789 4-4 4h-32v20c0 1.58-.93 3.012-2.371 3.654-.524.233-1.078.346-1.629.346zm-34.02-40 30.02 27.02v-15.02c0-2.209 1.789-4 4-4h32v-16h-32c-2.211 0-4-1.791-4-4v-15.02z"/></svg>',
		'next' => '<svg height="512" viewBox="0 0 128 128" width="512" xmlns="http://www.w3.org/2000/svg"><rect class="bg" x="5" y="5" width="115" height="115" rx="18" ry="18" /><path class="arrow" d="m104 124h-80c-11.027 0-20-8.973-20-20v-80c0-11.027 8.973-20 20-20h80c11.027 0 20 8.973 20 20v80c0 11.027-8.973 20-20 20zm-80-112c-6.617 0-12 5.383-12 12v80c0 6.617 5.383 12 12 12h80c6.617 0 12-5.383 12-12v-80c0-6.617-5.383-12-12-12zm40 92c-.551 0-1.105-.113-1.629-.346-1.441-.642-2.371-2.074-2.371-3.654v-20h-32c-2.211 0-4-1.791-4-4v-24c0-2.209 1.789-4 4-4h32v-20c0-1.58.93-3.012 2.371-3.654 1.441-.641 3.129-.375 4.305.682l40 36c.844.757 1.324 1.837 1.324 2.972s-.48 2.215-1.324 2.973l-40 36c-.75.673-1.707 1.027-2.676 1.027zm-32-32h32c2.211 0 4 1.791 4 4v15.02l30.02-27.02-30.02-27.02v15.02c0 2.209-1.789 4-4 4h-32z"/></svg>'
	),
	'arrow-style-8' => array(
		'prev' => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 490.667 490.667" style="enable-background:new 0 0 490.667 490.667;" xml:space="preserve"><rect class="bg" x="0" y="0" width="490.667" height="490.667" rx="12" ry="12" /><path class="arrow" d="M480,0H10.667C4.776,0,0,4.776,0,10.667V480c0,5.891,4.776,10.667,10.667,10.667H480c5.891,0,10.667-4.776,10.667-10.667 V10.667C490.667,4.776,485.891,0,480,0z M469.333,469.333h-448v-448h448V469.333z"/><path class="arrow" d="M301.792,380.875c4.237,4.093,10.99,3.976,15.083-0.261c3.993-4.134,3.993-10.688,0-14.821L196.416,245.333 l120.459-120.459c4.093-4.237,3.976-10.99-0.262-15.083c-4.134-3.993-10.687-3.993-14.821,0l-128,128 c-4.164,4.165-4.164,10.917,0,15.083L301.792,380.875z"/></svg>',
		'next' => '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
		  	 viewBox="0 0 490.667 490.667" style="enable-background:new 0 0 490.667 490.667;" xml:space="preserve">
		     <rect class="bg" x="0" y="0" width="490.667" height="490.667" rx="12" ry="12" />
		     <path class="arrow" d="M480,0H10.667C4.776,0,0,4.776,0,10.667V480c0,5.891,4.776,10.667,10.667,10.667H480c5.891,0,10.667-4.776,10.667-10.667
		       V10.667C490.667,4.776,485.891,0,480,0z M469.333,469.333h-448v-448h448V469.333z"/>
		     <path class="arrow" d="M173.792,380.875c4.165,4.164,10.917,4.164,15.083,0l128-128c4.164-4.165,4.164-10.917,0-15.083l-128-128
		       c-4.237-4.093-10.99-3.975-15.083,0.262c-3.992,4.134-3.992,10.687,0,14.82l120.459,120.459L173.792,365.792
		       C169.628,369.957,169.628,376.709,173.792,380.875z"/>
		  </svg>'
	),
	'arrow-style-9' => array(
		'prev' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" version="1.1" width="24" height="24" x="0" y="0" viewBox="0 0 511.9995 511" style="enable-background:new 0 0 512 512" xml:space="preserve"><circle class="bg" r="255.99975" cx="255.99975" cy="255.5" shape="circle"/><g transform="matrix(-0.8,0,0,0.8,460.7982421875,108.09600625038146)"><path xmlns="http://www.w3.org/2000/svg" d="m326.390625 380.460938c-6.507813 0-13.085937-1.265626-19.375-3.871094-18.832031-7.792969-31-25.929688-31-46.203125v-43.03125c0-11.046875 8.953125-20.003907 20-20.003907s20.003906 8.957032 20.003906 20.003907v43.03125c0 5.992187 4.402344 8.457031 6.292969 9.242187 1.925781.796875 6.871094 2.183594 11.183594-2.132812l132.664062-132.828125c7.816406-7.824219 7.816406-20.554688 0-28.378907l-132.664062-132.824218c-4.3125-4.316406-9.257813-2.933594-11.179688-2.136719-1.894531.785156-6.296875 3.253906-6.296875 9.246094v120.902343c0 22.058594-17.945312 40.003907-40.003906 40.003907h-256.015625c-11.046875 0-20-8.953125-20-20s8.953125-20.003907 20-20.003907h256.015625v-120.902343c0-20.273438 12.167969-38.414063 31-46.207031 18.898437-7.820313 40.402344-3.570313 54.78125 10.828124l132.664063 132.824219c23.382812 23.414063 23.382812 61.507813 0 84.921875l-132.664063 132.824219c-9.59375 9.605469-22.359375 14.691406-35.40625 14.695313zm0 0" class="arrow" style=""/></g></svg>',
		'next' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" version="1.1" width="24" height="24" x="0" y="0" viewBox="0 0 511.9995 511" style="enable-background:new 0 0 512 512" xml:space="preserve"><circle class="bg" r="255.99975" cx="255.99975" cy="255.5" shape="circle"/><g transform="matrix(0.8,0,0,0.8,51.19980468749998,103.09600625038141)"><path class="arrow" d="m326.390625 380.460938c-6.507813 0-13.085937-1.265626-19.375-3.871094-18.832031-7.792969-31-25.929688-31-46.203125v-43.03125c0-11.046875 8.953125-20.003907 20-20.003907s20.003906 8.957032 20.003906 20.003907v43.03125c0 5.992187 4.402344 8.457031 6.292969 9.242187 1.925781.796875 6.871094 2.183594 11.183594-2.132812l132.664062-132.828125c7.816406-7.824219 7.816406-20.554688 0-28.378907l-132.664062-132.824218c-4.3125-4.316406-9.257813-2.933594-11.179688-2.136719-1.894531.785156-6.296875 3.253906-6.296875 9.246094v120.902343c0 22.058594-17.945312 40.003907-40.003906 40.003907h-256.015625c-11.046875 0-20-8.953125-20-20s8.953125-20.003907 20-20.003907h256.015625v-120.902343c0-20.273438 12.167969-38.414063 31-46.207031 18.898437-7.820313 40.402344-3.570313 54.78125 10.828124l132.664063 132.824219c23.382812 23.414063 23.382812 61.507813 0 84.921875l-132.664063 132.824219c-9.59375 9.605469-22.359375 14.691406-35.40625 14.695313zm0 0" style=""/></g></svg>'
	),
	'arrow-style-10' => array(
		'prev' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" version="1.1" width="24" height="24" x="0" y="0" viewBox="0 0 123.958 123.959" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><circle class="bg" r="61.979" cx="61.979" cy="61.9795" shape="circle"/><g transform="matrix(-0.6,0,0,0.6,94.16719970703127,24.79160041809086)"><path class="arrow" d="M38.217,1.779c-3.8-3.8-10.2-1.1-10.2,4.2v112c0,5.3,6.4,8,10.2,4.2l56-56c2.3-2.301,2.3-6.101,0-8.401L38.217,1.779z" /></g></svg>',
		'next' => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" version="1.1" width="24" height="24" x="0" y="0" viewBox="0 0 123.958 123.959" class=""><circle class="bg" r="61.979" cx="61.979" cy="61.9795" shape="circle"/><g transform="matrix(0.6,0,0,0.6,34.79179992675785,24.79160041809086)"><path class="arrow" d="M38.217,1.779c-3.8-3.8-10.2-1.1-10.2,4.2v112c0,5.3,6.4,8,10.2,4.2l56-56c2.3-2.301,2.3-6.101,0-8.401L38.217,1.779z"/></g></svg>'
	),
	'arrow-morph-1' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><g id="next"><path fill="none" d="M0 0h24v24H0z"/><path d="M13.172 12l-4.95-4.95 1.414-1.414L16 12l-6.364 6.364-1.414-1.414z"/></g><g id="prev"><path fill="none" d="M0 0h24v24H0z"/><path d="M10.828 12l4.95 4.95-1.414 1.414L8 12l6.364-6.364 1.414 1.414z"/></g></svg>',
);
