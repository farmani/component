<div id="language-select">
    <?php
    /*
    if (sizeof($languages) < 4) {
        // Render options as links
        $lastElement = end($languages);
        foreach ($languages as $key => $lang) {
            if ($key != $currentLang) {
                echo CHtml::link($lang, $this->getOwner()->createMultilanguageReturnUrl(substr($key, 0, 2)));
            } else
                echo '<b>' . $lang . '</b>';
            if ($lang != $lastElement)
                echo ' | ';
        }
    }
    else {
        // Render options as dropDownList
        // Use: CHtml::form($action='', $method='post', $htmlOption=array())
        echo CHtml::form();
        // for each language, add a hidden field with the MultilanguageReturnUrl for that language.     
        foreach ($languages as $key => $lang) {
            echo CHtml::hiddenField($key, $this->getOwner()->createMultilanguageReturnUrl(substr($key, 0, 2)));
        }
        echo CHtml::dropDownList('language', $currentLang, $languages, array(
                'class' => 'dropdown span-3',
                'submit' => '',
            )
        );
        echo CHtml::endForm();
    }
    */
    ?>
    <div class="language-dropdown">
        <!--[if lte IE 6]><table><tr><td><![endif]-->
        <ul>
            <li><a href="#"><span><img src="http://generalb2b.net/themes/purple/images/<?= substr($currentLang, 0, 2);?>.png" alt="flag <?= $currentLang;?>"/></span><![if gt IE 6]></a><![endif]></li>
            <?php foreach ($languages as $key => $lang): ?>
            <?php if ($key != $currentLang): ?>
                <?php echo '<li><a href="' . $this->getOwner()->createMultilanguageReturnUrl(substr($key, 0, 2)) . '"><img src="http://generalb2b.net/themes/purple/images/' . substr($key, 0, 2) . '.png" alt="flag '.$key.'"/></a></li>'; ?>
            <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <!--[if lte IE 6]></td></tr></table></a><![endif]-->
    </div>
</div>