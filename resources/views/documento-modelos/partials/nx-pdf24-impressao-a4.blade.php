{{-- nx_pdf24_impressao_a4 — impressão A4 + ecrã (Calibri); exportações PDF24 (49,58333 × 70,08334 em) --}}
<style id="nx-pdf24-impressao-a4">
@media print {
	.pdf24_view {
		font-size: 1em;
		transform: scale(1);
	}
	.pdf24_05.pdf24_06 {
		height: 70.08334em !important;
		min-height: 70.08334em;
	}
	html,
	body {
		margin: 0;
		padding: 0;
		width: 210mm;
		min-height: 297mm;
		background: #fff;
	}
	body > div {
		margin: 0 !important;
		box-shadow: none !important;
	}
	.pdf24_.pdf24_02 {
		font-size: calc(210mm / 49.58333) !important;
		margin: 0 !important;
		box-sizing: border-box;
	}
	@page {
		size: 210mm 297mm;
		margin: 0;
	}
}
/*
 * Calibri/Arial só no ecrã. Na impressão, usar as fontes dos @font-face do export —
 * caso contrário as métricas mudam e o texto deixa de coincidir com o formulário.
 */
@media screen {
	body > div {
		box-shadow: 0 0 5px rgba(0,0,0,0.3) !important;
		margin: 20px auto !important;
	}
	.pdf24_.pdf24_02 {
		--nx-escala-fonte: 1;
		font-size: calc(1em * var(--nx-escala-fonte)) !important;
	}
	body,
	.pdf24_02,
	.pdf24_view,
	.pdf24_view *,
	.pdf24_01,
	.pdf24_01 span {
		font-family: Calibri, Arial, Helvetica, "Liberation Sans", sans-serif !important;
	}
}
</style>
