var tinyMCEImageList = new Array(
	<?$count = 0;?>
	<?foreach($this->SECTIONS->ATTACHMENTS->DATA as $row):?>
	["<?=$row->title;?>", "<?=$row->url;?>"]
	<?if($count++ < sizeof($this->SECTIONS->ATTACHMENTS->DATA) - 1):?>,<?endif;?>
	<?endforeach;?>
);