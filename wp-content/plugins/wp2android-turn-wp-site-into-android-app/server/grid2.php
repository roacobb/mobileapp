<?php
header('content-type: text/html; charset=iso-8859-2');
header('Access-Control-Allow-Origin: *');
require_once('function.php');
//20150422 add
$effect[1] = "scrollHorz";
$effect[2] = "flipHorz";
$effect[3] = "scrollVert";
$effect[4] = "scrollHorz";
$effect[5] = "scrollHorz";
$effect[6] = "tileSlide";
$effect[7] = "scrollHorz";
$effect[8] = "scrollVert";
$effect[9] = "scrollHorz";
$effect[10] = "flipVert";
$effect[11] = "tileSlide";

$delay[1] = -200;
$delay[2] = -1000;
$delay[3] = -1400;
$delay[4] = -800;
$delay[5] = -1100;
$delay[6] = -1800;
$delay[7] = -1900;
$delay[8] = -2500;
$delay[9] = -600;
$delay[10] = -700;
$delay[11] = -100;

$width[1] = 577;
$width[2] = 473;
$width[3] = 474;
$width[4] = 576;
$width[5] = 344;
$width[6] = 384;
$width[7] = 312;
$width[8] = 649;
$width[9] = 401;
$width[10] = 434;
$width[11] = 616;

$title_position[1] = "bottom";
$title_position[2] = "top";
$title_position[3] = "bottom";
$title_position[4] = "top";
$title_position[5] = "top";
$title_position[6] = "bottom";
$title_position[7] = "top";
$title_position[8] = "top";
$title_position[9] = "bottom";
$title_position[10] = "top";
$title_position[11] = "top";

$article_effect[1] = "grow";
$article_effect[2] = "zipper";
$article_effect[3] = "curl";
$article_effect[4] = "wave";
$article_effect[5] = "flip";
$article_effect[6] = "fly";
$article_effect[7] = "fly-simplified";
$article_effect[8] = "fly-reverse";
$article_effect[9] = "skew";
$article_effect[10] = "fan";
$article_effect[11] = "helix";

//20150422 add

$connect = dataxnam_base();

$qm = $connect->query("SELECT * FROM nta_message WHERE id = '1' AND online = '1'");
if ($qm->num_rows > 0) {

    $sqm = $qm->fetch_row();
    ?>
    <a href="message.html?id=1" class="message_alert"
       style="background-color:#<?php echo $sqm[1]; ?>; color:#<?php echo $sqm[2]; ?>;">
        <div style="padding:20px;"><?php echo $sqm[3]; ?></div>
    </a>
    <?php
}

//$question = $connect->query("SELECT * FROM wp_terms WHERE online = '1' ORDER BY id ASC");
$question = $connect->query("SELECT * FROM " . $table_prefix . "terms ORDER BY term_id ASC");
for ($lp = 0; $rz = $question->fetch_row(); ++$lp) {
    $tab[$lp] = $rz;
}
if ($question->num_rows > 0) {
    ?><?php
    foreach ($tab as $grid) {
        $i = 1;
        ?>
        <?php ?>
        <a href="article_list.html?id=<?php echo $grid[0]; ?>" class="cycle-slideshow menu_ico"
           data-cycle-fx='<?php echo $grid[2]; ?>' data-cycle-delay="<?php echo $grid[3]; ?>"
           style="width:<?php echo $grid[4]; ?>px;">
            <div class="cycle-overlay" style="<?php echo $grid[5]; ?>:0px;">
                <div class="main_txt"><?php echo $grid[1]; ?></div>
            </div>
            <?php ?>
            <?php /* ?>
			<a href="article_list.html?id=<?php echo $grid[0]; ?>" class="cycle-slideshow menu_ico" data-cycle-fx='scrollHorz<?php //echo $effect[$i]; ?>' data-cycle-delay="-200<?php //echo $delay[$i]; ?>" style="width:577<?php //echo $width[$i]; ?>">
			<div class="cycle-overlay" style="bottom<?php //echo $title_position[$i]; ?>:0px;"><div class="main_txt"><?php echo $grid[1];?></div></div>
			<?php */ ?>
            <?php
            $i++;
            $question_2 = $connect->query("SELECT * FROM nta_grid_photo WHERE id_grid = '$grid[0]' ORDER BY id ASC");
            for ($lp_2 = 0; $rz_2 = $question_2->fetch_row(); ++$lp_2) {
                $tab_2[$lp_2] = $rz_2;
            }
            $pn = 1;
            if ($question_2->num_rows > 0) {
                foreach ($tab_2 as $pt) {
                    ?>
                    <img src="http://i-tivi.com/nta_server/images/tails/<?php echo $pt[2]; ?>"
                         <?php if ($pn == 1){ ?>class=first<?php } ?>>
                    <?php
                    $pn++;
                }
                unset($tab_2);
            }
            ?>
        </a>
        <?php

    }
} else {
    ?>

    <div class="alert_bg">
        <div style="padding:20px 50px 20px 50px;">No entries in this category</div>
    </div>

    <?php
}
?>
