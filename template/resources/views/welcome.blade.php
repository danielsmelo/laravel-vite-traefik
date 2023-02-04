<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags-->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="au theme template">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="author" content="Hau Nguyen">
  <meta name="keywords" content="au theme template">

  <!-- Title Page-->
  <title>Forms</title>

  <!-- Fontfaces CSS-->

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
  <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
  @vite(['resources/js/app.js', 'resources/css/app.css', 'resources/vendor/bootstrap-4.1/bootstrap.min.css'])

</head>

<body>
  <div class="page-wrapper">

    <!-- MENU SIDEBAR-->
    <aside class="menu-sidebar d-none d-lg-block">
      <div class="logo">
        <a href="#">

        </a>
      </div>
      <div class="menu-sidebar__content js-scrollbar1">
        <form method="POST" id="form-create">
          <div class="navbar-sidebar">
            <div class="row form-group">
              <div class="col col-sm-12">
                <label for="cc-payment" class="control-label mb-1">Tempo de Simulação</label>
                <input type="text" placeholder="" class="form-control" id="quantity">
              </div>
              <div class="col col-sm-12">
                <label for="cc-payment" class="control-label mb-1">Média de TS</label>
                <input type="text" placeholder="" class="form-control" id="ts-average">
              </div>
              <div class="col col-sm-12">
                <label for="cc-payment" class="control-label mb-1">Variação TS</label>
                <input type="text" placeholder="" class="form-control" id="ts-variation">
              </div>
              <div class="button-custom">
                <button type="submit" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"
                  id="btn-submit">Simular</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </aside>
    <!-- END MENU SIDEBAR-->

    <!-- PAGE CONTAINER-->
    <div class="page-container">

      <!-- MAIN CONTENT-->
      <div class="main-content">
        <div class="section__content section__content--p30">
          <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
              <h6 class="m-0 font-weight-bold text-primary">Resultado</h6>
            </div>
            <div class="card-body">
              <div id="procedimenti">
              </div>
            </div>
            <button id="load-more" class="btn btn-primary">Carregar Mais</button>
          </div>
        </div>
        <div class="section__content section__content--p30">
          <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
              <h6 class="m-0 font-weight-bold text-primary">Tabela Usada</h6>
            </div>
            <div class="card-body">
              <img src="{{ Vite::asset('resources/images/table.png') }}">
            </div>
          </div>
        </div>
      </div>
    </div>phpinfo();
  </div>

  <script type="text/javascript">
    function getFormData() {
      const formData = new FormData()

      formData.append('quantity', $('#quantity').val())
      formData.append('tsAverage', $('#ts-average').val())
      formData.append('tsVariation', $('#ts-variation').val())

      return formData
    }

    jQuery(document).ready(function($) {

      let page = 1;

      var arrProcedimenti = [];

      var initialValues = '{{ $users }}';
      var initialValues = JSON.parse(initialValues.replace(/&quot;/g, '"'));

      if (page == 1) {

        console.log(initialValues);

        $.each(initialValues, function(index, val) {
          arrProcedimenti.push([val.name, val.email]);
        });

      }

      $('#procedimenti').html(
        '<table class="table table-striped table-bordered" cellpadding="0" cellspacing="0" border="1" class="display" id="tbprocedimenti"></table>'
      );

      var oTable = $('#tbprocedimenti').DataTable({
        "data": arrProcedimenti,
        "order": [],
        "columns": [{
            "title": "Nome"
          },
          {
            "title": "Email"
          }
        ],

      });

      $("#load-more").click(function() {
        page++;
        console.log(page);
        $.ajax({
          type: 'post',
          url: '{{ route('simulate') }}',
          data: JSON.stringify({
            'page': page
          }),
          contentType: "application/json; charset=utf-8",
          traditional: true,
        }).done(function(response) {
          if (response.success) {
            console.log(response.result);
            var resultArray = JSON.parse(response.result);
            console.log(response.pageverify);
            oTable.clear();
            arrProcedimenti = [];

            resultArray.forEach(function(item) {
              arrProcedimenti.push([
                item.name,
                item.email
              ]);
            });
            for (var k = 0; k < arrProcedimenti.length; k++) {
              oTable.row.add(arrProcedimenti[k]);
            }

            oTable.draw();
          }
        }).fail(function(err) {
          let message = 'Erro ao criar a simulação'
          if (err.responseJSON && err.responseJSON.errors) {
            console.log('err.responseJSON.errors', err.responseJSON.errors)
            message = Object.values(err.responseJSON.errors)[0].join('\n')
          }
          Swal.fire({
            title: 'Ops',
            text: message,
            type: 'error',
            icon: 'error',
          })
        }).always(function() {
          $('#btn-submit').attr('disabled', false)
        });
      });
    });
  </script>

</body>

</html>
<!-- end document-->
