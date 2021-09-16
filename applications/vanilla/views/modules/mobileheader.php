
<?php if($this->back) { ?>
    <a href="<?= url('/discussions') ?>" class="d-mobile">
        <svg width="26" height="18" viewBox="0 0 26 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M24.25 8.88715L1.75 8.88715" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9.11842 16.2175L1.77539 8.87444L9.11842 1.53141" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>
<?php } ?>
<h1 class="d-desktop"><a href="<?= url('/discussions') ?>"><?php echo t('Help Zone')?></a></h1>
<h3 class="d-mobile"><?php echo t($this->title ?? 'Help Zone')?></h3>
