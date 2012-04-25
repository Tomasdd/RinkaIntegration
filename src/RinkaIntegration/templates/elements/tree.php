<?php if (!isset($compact)) $compact = true; ?>
<style>
    .horizontalMenu {
        position: relative;
        padding: 10px 0;
    }
    .horizontalMenu ul.notLast {
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAKCAQAAADBEsxyAAAAAXNSR0IArs4c6QAAAAJiS0dEAP+Hj8y/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH2wcbCCoXY75SlQAAAHBJREFUGNN1y0EOwVAYReHvSScSTOzAJizAWjSRWpDE+iSVdEYilP4mhOpzRjc55/JiGzKM3iOyQaKKGxauars0eIeVpamZuU1kdO2gcTY2UfaC4jPvWhf9e0FodJLWycM+DfQRhU73I78oYx3+k5dP1bglLaveHDMAAAAASUVORK5CYII=) no-repeat right 3px #F1F0F5;
    }
    .horizontalMenu ul {
        position: absolute;
        margin: 0 -1px 0 0 !important;
        padding: 1px 20px 1px 5px;
        height: <?php echo $compact ? '16px' : 'auto' ?>;
        overflow: hidden;

        color: #26343D;
        background-color: #F1F0F5;
        border: 1px solid #DBDDE3;
        
        font-size: inherit !important;
        line-height: inherit !important;
    }
    .horizontalMenu ul:hover {
        height: auto;
    }
    .horizontalMenu ul li {
        display: block;
        padding: 0 !important;
        margin: 0;
        height: 20px;
        white-space: nowrap;
        cursor: pointer;
        background: none !important;
    }
    .horizontalMenu ul li a {
        color: inherit;
    }

    .horizontalMenu .ul {
        display: block;
        float: left;
        position: relative;
    }

    .horizontalMenu ul.fake {
        position: static;
        visibility: hidden;
    }

</style>
<script type="text/javascript">
    initHTree(<?php echo json_encode($tree); ?>, <?php echo $compact ? 'true' : 'false'?>);
</script>
<div class="horizontalMenu">
    <?php echo $this->element('tree_part', array('tree' => $tree, 'compact' => $compact)); ?>
    <div style="clear: both;"></div>
</div>
<div style="clear: both"></div>