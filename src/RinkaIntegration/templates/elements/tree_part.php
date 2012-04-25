<?php if (!isset($zindex)) $zindex = 200; ?>
<div class="ul">
    <ul style="z-index: <?php echo intval($zindex); ?>">
    <?php $fake = ''; ?>
    <?php $selectedNode = null; ?>
    <?php foreach ($tree as $id => $node): ?>
        <?php if (!empty($node['selected'])) $selectedNode = $node; ?>
        <?php $text = $this->escape($node['title']); ?>
        <?php if ($node['current']) $text = '<strong>' . $text . '</strong>'; ?>
        <li data-id="<?php echo $id; ?>">
            <?php if (isset($node['link'])): ?>
                <a href="<?php $this->write($node['link']); ?>">
                    <?php echo $text; ?>
                </a>
            <?php else: ?>
                <?php echo $text; ?>
            <?php endif; ?>
        </li>
        <?php $fake .= '<li>' . $text . '</li>'; ?>
    <?php endforeach; ?>
    </ul><ul class="fake"><?php echo $fake; ?></ul>
</div>
<?php if ($selectedNode === null && $compact) $selectedNode = reset($tree); ?>
<?php if (isset($selectedNode['children'])): ?>
    <?php echo $this->element('tree_part', array('tree' => $selectedNode['children'], 'zindex' => $zindex - 1, 'compact' => $compact)); ?>
<?php endif; ?>