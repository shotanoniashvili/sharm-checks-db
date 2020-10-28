<table>
  <thead>
  <tr>
    <th>ჩეკის დასახელება:</th>
    <th>{{ $check->name }}</th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
  </tr>
  <tr>
    <th>#</th>
    <th>ოპერაციის ანგ. დასახელება</th>
    <th>GEL</th>
    <th>EUR</th>
    <th>RUR</th>
    <th>USD</th>
    <th>ბანკი/სალ.</th>
    <th>მიმღები</th>
    <th>ბრენდი</th>
    <th>შესაბამ. საბუთი</th>
    <th>კომენტარი</th>
    <th>{{ $check->user->name }}</th>
    @foreach($check->user->managers as $manager)
      <th>{{ $manager->name }}</th>
    @endforeach
  </tr>
  </thead>
  <tbody>
  @foreach($check->items as $key => $item)
    <tr>
      <td>{{ $key }}</td>
      <td>{{ $item->op_name }}</td>
      <td>{{ $item->gel }}</td>
      <td>{{ $item->eur }}</td>
      <td>{{ $item->rur }}</td>
      <td>{{ $item->usd }}</td>
      <td>{{ $item->source }}</td>
      <td>{{ $item->destination }}</td>
      <td>{{ $item->brand }}</td>
      <td>{{ $item->doc_type }}</td>
      <td>{{ $item->comment }}</td>
      <td>{{ $check->user->name }}</td>
      @foreach($check->user->managers as $manager)
        <th>&nbsp;</th>
      @endforeach
    </tr>
  @endforeach
  </tbody>
</table>
