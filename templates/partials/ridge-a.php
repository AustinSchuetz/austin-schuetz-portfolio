<?php /* RIDGE-A — alpine (default). Handoff production path data; 4th path = ground of the next section. */ ?>
<div class="ridge ridge--a ridge--tint-<?= e($tint ?? 'green') ?><?= !empty($flip) ? ' ridge--flip' : '' ?>" aria-hidden="true">
    <svg viewBox="0 0 1440 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path class="ridge__l1" d="M0,58 L80,34 L150,50 L260,18 L340,46 L430,30 L560,54 L640,26 L760,48 L860,20 L950,44 L1060,30 L1150,52 L1260,24 L1350,44 L1440,32 L1440,100 L0,100 Z"/>
        <path class="ridge__l2" d="M0,72 L110,44 L210,62 L330,36 L470,64 L590,42 L700,60 L820,38 L940,62 L1050,44 L1170,64 L1290,40 L1380,58 L1440,48 L1440,100 L0,100 Z"/>
        <path class="ridge__l3" d="M0,86 L130,60 L250,78 L390,54 L540,80 L680,58 L800,76 L950,56 L1080,78 L1210,60 L1330,76 L1440,62 L1440,100 L0,100 Z"/>
        <path class="ridge__ground" d="M0,96 L160,76 L320,90 L500,72 L700,92 L900,74 L1100,90 L1280,76 L1440,88 L1440,100 L0,100 Z"/>
    </svg>
</div>
