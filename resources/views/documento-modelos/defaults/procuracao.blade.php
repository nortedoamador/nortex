<!DOCTYPE html>
<html>
	<head>
      <title>PROCURAÇÃO</title>
<style>
p{
	font-family:Arial, Helvetica, sans-serif;
	margin-top: 0;
   	margin-bottom: 0;
}
p.texto {
  line-height: 1.5;
  font-size: 18px;
  text-align: justify;
}
body {
    width: 794px; /* 210 mm a 300 dpi */
    height: 1113px; /* 297 mm a 300 dpi */
    margin: 0.3cm auto; /* Ajuste as margens */
}
</style>
</head>
	<body>
<table style="border-collapse: collapse; width: 76.3651%; height: 608px;" border="0" align="center">
<tbody>
<tr>
<td style="width: 100%;">
<p>&nbsp;</p>
@if(!empty($logo_empresa_url))
<p><img style="display: block; margin-left: auto; margin-right: auto;" src="{{ $logo_empresa_url }}" alt="{{ $nome_empresa }}" width="360" height="140" /></p>
@endif
</td>
</tr>
<tr>
<td style="width: 100%;">

<p class="western" align="center">
<span style="font-size: small;">
<strong><b>{{ $nome_empresa }}</b></strong>
</span>
</p>

<p class="western" align="center">
<span style="font-size: small;">
<strong><b>{{ $cnpj_empresa }}</b></strong>
</span>
</p>

<p class="western" align="center">
<span style="font-size: small;">
<strong><b>{{ $cidade_uf_empresa }}</b></strong>
</span>
</p>

<p class="western" align="center" style="margin-bottom: 50px;">
<span style="font-size: small;">
<strong>Tel. (XX) XXXXX- XXXX</strong>
</span>
</p>

<p class="western" align="center">
<span style="font-size: x-large;">
<strong>PROCURA&Ccedil;&Atilde;O</strong>
</span>
</p>

<p class="western" align="justify">&nbsp;</p>

<p class="texto">
Pelo presente instrumento particular de procuração, eu, 
<b>{{ $nome_cliente }}</b>, 
inscrito no CPF: <b>{{ $cpf_cliente }}</b>, 
RG: <b>{{ $rg_cliente }}</b> 
E <b>{{ $orgao_emissor_cliente }}</b>, 
brasileiro(a), residente 
<b>{{ $endereco_cliente }}</b>, 
Contato: <b>{{ $contato_cliente }}</b>.
</p>

<br>

<p class="texto">
{!! nl2br(e($texto_procuracao_procuradores)) !!}
</p>

<br>

<p class="western" align="justify">&nbsp;</p>
<p class="western" align="justify">&nbsp;</p>

<p class="western" align="right">
<span style="font-size: 18px;">{{ $cidade_uf_empresa }}, ____ em ____________ </span>
<span style="font-size: medium;">de _______ .</span>
</p>

<p class="western" align="left">&nbsp;</p>
<p class="western" align="left">&nbsp;</p>
<p class="western" align="left">&nbsp;</p>
<p class="western" align="left">&nbsp;</p>
<p class="western" align="left">&nbsp;</p>

<p class="western" style="text-align: center;" align="left">
<a name="_GoBack"></a>________________________________________________<u></u>
</p>

<p class="western" style="text-align: center;" align="center">
<span style="font-size: medium;">Assinatura do Outorgante</span>
</p>

<p class="western" style="text-align: center;" align="center">
<span style="font-size: medium;">(Firma Reconhecida)</span>
</p>

<p class="western" align="justify">&nbsp;</p>

</td>
</tr>
</tbody>
</table>
	</body>
</html>