<?php
$path = public_path($img[0]->diretory);
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>
<table style="width:70%; position:absolute; left: 138px;">
   <tr>
        <td  style=" font-size:12px; background-color: black;color: white; height: auto; ">PACKING LIST</td>
        <td  style=" font-size:12px;">{{$head->code}}</td>
  </tr>
  <tr>
    <td style=" font-size:12px; background-color: black;color: white;">DATE</td>
    <td style="font-size:12px;">{{formatDate($head->taxDate)}} </td>
  </tr>
</table>
<img src="{{$base64}}" alt="Girl in a jacket" width="100" height="120">
<br>
<table id="t01">
  <tr>
    <th id="th2" style="width:130px; font-size:12px;">EXPORTER</th>
  </tr>
  <tr>
    @foreach($company as $value)
      <td colspan="2" style="font-size:12px;"> {{$value->company}}<br>
      {{$value->address}}, LOTE:{{$value->number}} {{$value->neighborhood}} {{$value->city}} {{$value->cep}}<br>  CNPJ:{{$value->cnpj}}</td>
    @endforeach
  </tr>
</table>
<br>
<table id="t01">
  <tr>
    <th style="width:130px; font-size:12px;">IMPORTER</th>
  </tr>
  <tr>
    @foreach($partner as $value)
      <td colspan="2" style=" font-size:15px;">{{$value['CardName']}}<br>
      {{$value['Street']}} {{$value['StreetNo']}}  {{$value['Block']}}<br>
      {{$value['City']}} {{$value['State']}}</td>
      @endforeach
  </tr>
</table>
<br>
<br>
<table id="t02" style="width: 100%">
  <tr>
    <th style="font-size:11px;">CONTAINER</th>
    <th style="font-size:11px;">DESCRIPTION</th>
    <th style="font-size:11px;">GROSS WEIGHT(KG)</th>
    <th style="font-size:11px;">NET WEIGHT(KG)</th>
    <th style="font-size:11px;">MEASUREM (M³)</th>
    <th style="font-size:11px;">QTY</th>
    <th style="font-size:11px;">EMB</th>
  </tr>
  @if(isset($body) && !empty($body) && isset($head))
   @foreach($body as $key => $value)
     <tr>
       <td>{{$value->containerCode}}</td>
       <td>{{$value->description}}</td>
       <td>{{$value->grossWeigth}}</td>
       <td>{{$value->netWeigth}}</td>
       <td>{{$value->measurem}}</td>
       <td>{{$value->quantity}}</td>
       <td>{{$value->packing}}</td>
     </tr>
   @endForeach
 @endif
</table>
<br>
<table style="width:50%; padding-top:150px">
  <tr>
    <th style="width:100px; font-size:12px;">GROSS WEIGHT(KG)</th>
    <th style="width:100px; font-size:12px;">{{$head->grossWeigth}}</th>
  </tr>
  <tr>
    <th style="width:100px; font-size:12px;">NET WEIGHT(K2):</th>
    <th style="width:100px; font-size:12px;">{{$head->netWeigth}}</th>
  </tr>
  <tr>
    <th style="width:100px; font-size:12px;">VOLUME TYPE:</th>
    <th style="width:100px; font-size:12px;">{{$head->volumeType}}</th>
  </tr>
    <tr>
        <th style="width:100px; font-size:12px;">PRICE/TON:</th>
        <th style="width:100px; font-size:12px;">{{$head->price_tonne}}</th>
    </tr>
</table>
<br>
<br>
<table style="width:50%;">
    <tr>
        <th style="width:100px; font-size:12px;">Booking: </th>
        <th style="width:100px; font-size:12px;">{{$head->booking}}</th>
    </tr>
    <tr>
        <th style="width:100px; font-size:12px;">Destination port: </th>
        <th style="width:100px; font-size:12px;">{{$head->destination_port}}</th>
    </tr>
</table>

</body>
<style>
table {
    width:90%;
}
table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
}
th, td {
    padding: 8px;
    text-align: left;
}
table#t01 tr:nth-child(even) {

}
table#t02 {
  width:70%;
}
table#t02 th {
    background-color: black;
    color: white;
}
table#t01 tr:nth-child(odd) {
   background-color: #fff;
}
table#t01 th {
    background-color: black;
    color: white;
}
</style>
