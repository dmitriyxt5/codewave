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
					src="/images/slider/1.jpeg"
					alt="Образец 1"
					class="w-full h-auto max-h-96 object-contain rounded-lg"
				/>
			</SplideSlide>

			<!-- Слайд 2 -->
			<SplideSlide class="flex justify-center items-center">
				<img
					src="/images/slider/2.jpeg"
					alt="Образец 2"
					class="w-full h-auto max-h-96 object-contain rounded-lg"
				/>
			</SplideSlide>
			<SplideSlide class="flex justify-center items-center">
				<img
					src="/images/slider/3.jpeg"
					alt="Образец 2"
					class="w-full h-auto max-h-96 object-contain rounded-lg"
				/>
			</SplideSlide>
			<SplideSlide class="flex justify-center items-center">
				<img
					src="/images/slider/4.jpeg"
					alt="Образец 2"
					class="w-full h-auto max-h-96 object-contain rounded-lg"
				/>
			</SplideSlide>

			<!-- Добавь больше слайдов по аналогии -->
		</Splide>
	</div>
</template>
