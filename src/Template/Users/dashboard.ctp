<?php
if (!function_exists('buildInstructionalVideoEmbedUrl')) {
	function buildInstructionalVideoEmbedUrl($videoLink)
	{
		$videoLink = trim((string)$videoLink);
		if ($videoLink === '') {
			return '';
		}

		if (preg_match('/youtu\.be\/([A-Za-z0-9_-]{6,})/i', $videoLink, $matches)) {
			return 'https://www.youtube.com/embed/' . $matches[1];
		}

		if (preg_match('/(?:v=|embed\/)([A-Za-z0-9_-]{6,})/i', $videoLink, $matches)) {
			return 'https://www.youtube.com/embed/' . $matches[1];
		}

		if (preg_match('/^[A-Za-z0-9_-]{6,}$/', $videoLink)) {
			return 'https://www.youtube.com/embed/' . $videoLink;
		}

		return $videoLink;
	}
}

$instructionalVideos = [];
if (!empty($settingsD)) {
	$videoLinks = [];
	$videoLinksFile = WWW_ROOT . 'files' . DS . 'dashboard_video_links.json';
	if (is_file($videoLinksFile)) {
		$fileVideoLinks = json_decode((string)@file_get_contents($videoLinksFile), true);
		if (is_array($fileVideoLinks)) {
			$videoLinks = $fileVideoLinks;
		}
	}
	if (!empty($settingsD->video_links_json)) {
		$decodedVideoLinks = json_decode((string)$settingsD->video_links_json, true);
		if (is_array($decodedVideoLinks)) {
			$videoLinks = $decodedVideoLinks;
		}
	}

	if (empty($videoLinks)) {
		for ($i = 1; $i <= 9; $i++) {
			$fieldName = 'video_link_' . $i;
			if (!empty($settingsD->{$fieldName})) {
				$videoLinks[] = $settingsD->{$fieldName};
			}
		}
	}

	$normalizedVideoLinks = [];
	foreach ((array)$videoLinks as $videoLink) {
		if (!is_scalar($videoLink)) {
			continue;
		}

		$videoLink = trim((string)$videoLink);
		if ($videoLink === '') {
			continue;
		}

		$normalizedVideoLinks[] = $videoLink;
	}

	foreach (array_values($normalizedVideoLinks) as $index => $videoLink) {
		$instructionalVideos[] = [
			'title' => 'Video ' . ($index + 1),
			'url' => buildInstructionalVideoEmbedUrl($videoLink),
		];
	}
}

$dashboardUser = (object) ['first_name' => '', 'email_address' => '', 'user_type' => ''];
if (isset($userDetails) && is_object($userDetails)) {
	$dashboardUser = $userDetails;
}
?>
<style>
	.instructional-videos-section {
		margin-top: 1.75rem;
	}

	.instructional-videos-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
		gap: 1rem;
		margin-top: 1rem;
		align-items: stretch;
	}

	.instructional-video-card {
		background: #fff;
		border: 1px solid rgba(28, 36, 82, 0.12);
		border-radius: 16px;
		padding: 0.9rem;
		box-shadow: 0 10px 24px rgba(28, 36, 82, 0.08);
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.instructional-video-title {
		font-size: 0.98rem;
		font-weight: 600;
		margin-bottom: 0.75rem;
		color: #1c2452;
		min-height: 2.9rem;
		display: flex;
		align-items: flex-start;
	}

	.instructional-video-frame {
		position: relative;
		padding-top: 56.25%;
		border-radius: 12px;
		overflow: hidden;
		background: #111;
		flex: 1;
	}

	.instructional-video-frame iframe {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		border: 0;
	}

	.instructional-videos-intro {
		max-width: 70ch;
		color: #3d4668;
	}
</style>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<h2 class="mt-3">Dashboard</h2>
				<?php if ($dashboardUser->user_type === 'Student') { ?>
				<div class="dasboard-section">
					<div class="dashboard-text">
							<h2>Welcome <?php echo h($dashboardUser->first_name); ?></h2>
						<div class="instructional-videos-section">
							<h3>Instructional Videos</h3>
							<p class="instructional-videos-intro">Please see the instructional videos below for navigation of the Convention Portal. For any other questions, please contact the events team.</p>
							<?php if (!empty($instructionalVideos)) { ?>
								<div class="instructional-videos-grid">
									<?php foreach ($instructionalVideos as $video) { ?>
										<div class="instructional-video-card">
											<div class="instructional-video-title"><?php echo h($video['title']); ?></div>
											<div class="instructional-video-frame">
												<iframe src="<?php echo h($video['url']); ?>" title="<?php echo h($video['title']); ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
											</div>
										</div>
									<?php } ?>
								</div>
							<?php } else { ?>
								<p class="instructional-videos-intro">No instructional videos are configured yet.</p>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<!-- dashboard-section-1 start-->
				<div class="dasboard-section">
					<div class="dashboard-text">
						<h2>Welcome <?php echo $dashboardUser->first_name; ?> (<?php echo $dashboardUser->email_address; ?>)</h2>
						
						
						<?php
						if (!empty($settingsD) && !empty($settingsD->postinfo))
						{
							echo '<p>';
							
							echo $postinfo = $settingsD->postinfo;
							
							echo '</p>';
							
						}
						?>
						
						<?php if (!empty($instructionalVideos)) { ?>
							<p class="instructional-videos-intro">Please see the instructional videos below for navigation of the Convention Portal. For any other questions, please contact the events team.</p>
							<div class="instructional-videos-grid">
								<?php foreach ($instructionalVideos as $video) { ?>
									<div class="instructional-video-card">
										<div class="instructional-video-title"><?php echo h($video['title']); ?></div>
										<div class="instructional-video-frame">
											<iframe src="<?php echo h($video['url']); ?>" title="<?php echo h($video['title']); ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
										</div>
									</div>
								<?php } ?>
							</div>
						<?php } else { ?>
							<p class="instructional-videos-intro">No instructional videos are configured yet.</p>
						<?php } ?>
						
						<p>&nbsp;</p>
						<p>&nbsp;</p>
						<p>&nbsp;</p>
						
					</div>
				</div>
				<!-- dashboard-section-1 end-->
			<?php } ?>
			
		</main>
	</div>
</div>