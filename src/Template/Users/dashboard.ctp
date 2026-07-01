<?php
$instructionalVideos = [
	['title' => 'ACP 1: Introduction and Registration', 'url' => 'https://www.youtube.com/embed/r398Y2db2nc?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX'],
	['title' => 'ACP 2: Global List Information', 'url' => 'https://www.youtube.com/embed/dcBTlI2_w20?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX'],
	['title' => 'ACP 3: Price Structure, Supervisor and Student Registration', 'url' => 'https://www.youtube.com/embed/Zk2dhRuNsDo?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX'],
	['title' => 'ACP 4: Student Event Registration', 'url' => 'https://www.youtube.com/embed/Cyn9-uJKeuY?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX'],
	['title' => 'ACP 5: Events of the Heart', 'url' => 'https://www.youtube.com/embed/6G-03VkSMdY?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX'],
	['title' => 'ACP 6: Judges Portal Tutorial', 'url' => 'https://www.youtube.com/embed/G4vxpK0kzPQ?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX'],
	['title' => 'ACP 7: How To Register As A Judge', 'url' => 'https://www.youtube.com/embed/mYBJPgexNmk'],
	['title' => 'ACP 8: Results List and Judges Forms', 'url' => 'https://www.youtube.com/embed/uysBVmzqGXU?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX'],
];
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
						</div>
					</div>
				</div>
			<?php } else { ?>
				<!-- dashboard-section-1 start-->
				<div class="dasboard-section">
					<div class="dashboard-text">
						<h2>Welcome <?php echo $dashboardUser->first_name; ?> (<?php echo $dashboardUser->email_address; ?>)</h2>
						
						
						<?php
						if(!empty($settingsD->postinfo))
						{
							echo '<p>';
							
							echo $postinfo = $settingsD->postinfo;
							
							echo '</p>';
							
						}
						?>
						
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