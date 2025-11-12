<template>
	<div class="flex justify-center py-8 px-4">
		<Splide
			class="w-full max-w-5xl"
			:options="{ rewind: true, arrows: true, pagination: true }"
			aria-label="Изображения"
		>
			<!-- Слайд 1 -->
			<SplideSlide class="flex justify-center items-center">
				<img
					src="https://kipk.edu.kz/wp-content/uploads/2020/10/IMG_3916-2-1120x600.jpg"
					alt="Образец 1"
					class="w-full h-auto max-h-96 object-contain rounded-lg"
				/>
			</SplideSlide>

			<!-- Слайд 2 -->
			<SplideSlide class="flex justify-center items-center">
				<img
					src="/images/photo_2025-11-11_19-38-03.jpg"
					alt="Образец 2"
					class="w-full h-auto max-h-96 object-contain rounded-lg"
				/>
			</SplideSlide>

			<!-- Добавь больше слайдов по аналогии -->
		</Splide>
	</div>
</template>
