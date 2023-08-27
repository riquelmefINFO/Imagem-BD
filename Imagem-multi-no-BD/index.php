<?php

//mostra dados das imagens
//var_dump($_FILES['arquivo']) ;

//incluindo conexao
include("conexao.php");

//verifica se existi error na url
if (isset($_GET['error'])) {
    //recebe o id pela url
    $error = intval($_GET['error']);

    //erro 001
    if ($error == 1) {
        $resultado = [false, "Falha ao enviar arquivo"];
        //erro 002
    } elseif ($error == 2) {
        $resultado = [false, "Arquivo muito grande!! Max: 2MB"];
        //erro 003
    } elseif ($error == 3) {
        $resultado = [false, "Tipo de arquivo não aceito"];
    }
}

//verifica se existi ação na url e mostra
if (isset($_GET['acao'])) {
    $resultado = [true, $_GET['acao']];
}

//verifica se existi deletar na url
if (isset($_GET['deletar'])) {

    //recebe o id pela url
    $id = intval($_GET['deletar']);
    //sql que seleciona tudo quando o id for igual ao da url
    $sql_query = $mysqli->query("SELECT * FROM arquivos WHERE id = '$id'") or die($mysqli->$error);
    $arquivo = $sql_query->fetch_assoc();

    //if verifica se unlink(serve para deletar arquivo) excluiu o arquivo
    if (unlink($arquivo['path'])) {
        //sql para deletar quando id for igual ao da url
        $deu_certo = $sql_query = $mysqli->query("DELETE FROM arquivos WHERE id = '$id'") or die($mysqli->$error);
        //verificar se o sql deu certo
        if ($deu_certo) {
            //voltar para a pagina sem o "delete(id)" na url
            header("location: index.php?acao=Arquivo excluido com sucesso!");
        }
    }
}

//função para inserir a imagem no BD
function enviarArquivo($error, $size, $name, $tmp_name)
{
    //inluindo conexao
    include("conexao.php");

    //pasta para onde vão os arquivos
    $pasta = "arquivos/";
    //nome do arquivo
    $nomeDoArquivo = $name;
    //gerar um nome aleatorio
    $novoNomeDoArquivo = uniqid();
    //strtolower converte tudo para minusculo
    $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));
    //caminho da imagem é: nome da pasta + arquivo . extensão
    $path = $pasta . $novoNomeDoArquivo . "." . $extensao;

    //Caso falhar
    if ($error) {
        //volta para a pagina com o error 001
        die(header("location: index.php?error=001"));
    }

    //verificar se o arquivo é maior que 2MB
    if ($size > 2097152) {
        //volta para a pagina com o error 002
        die(header("location: index.php?error=002"));
    }

    //verificar se a extensão é valida
    if ($extensao != "jpg" && $extensao != "png") {
        //volta para a pagina com o error 003
        die(header("location: index.php?error=003"));
    }

    //mover arquivo para o caminho path
    $deu_certo = move_uploaded_file($tmp_name, $path);
    //verificar se deu certo
    if ($deu_certo) {
        //inserir o nome e caminho do arquivo
        $mysqli->query("INSERT INTO arquivos (nome, path) VALUES ('$nomeDoArquivo', '$path')") or die($mysqli->$error);
        return true;
    } else {
        return false;
    }
}

//verificar se existi um arquivo
if (isset($_FILES['arquivo'])) {
    //arquivo recebe os dados da imagem
    $arquivo = $_FILES['arquivo'];
    //$tudo_certo = verdadeiro
    $tudo_certo = true;
    //foreach roda enquanto existir arquivo
    foreach ($arquivo['name'] as $index => $arq) {
        //chama a função dando seus respectivos valores
        $tudo_certo = enviarArquivo($arquivo['error'][$index], $arquivo['size'][$index], $arquivo['name'][$index], $arquivo["tmp_name"][$index]);
        //caso der erro na função, $tudo_certo = falso
        if (!$tudo_certo) {
            $tudo_certo = false;
        }
    }
    if ($tudo_certo) {
        //se for verdadeiro, mostre
        $resultado = [true, "Enviado(s) com sucesso!"];
    } else {
        //se for falso, mostre
        $resultado = [false, "Falha ao enviar um ou mais arquivos!"];
    }
}

//pegando do banco todos os arquivos
$sql_query = $mysqli->query("SELECT * FROM arquivos") or die($mysqli->$error);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Arquivos</title>
    <!--boostrap 5.3-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.2/assets/css/docs.css" rel="stylesheet">
    <!--DataTable-->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.css">

</head>

<body class="bg-dark">

    <?php

    //se existir $resultado
    if (isset($resultado)) {

        //resultado recebe a mensagem do erro
        $mensagem = $resultado[1];

        //mostar a notificação
        echo "
        <div aria-live='polite' aria-atomic='true'>
            <!-- Then put toasts within -->
            <div class='toast position-absolute top-50 start-50 translate-middle bg-dark text-white' style='z-index: 1090;'
                role='alert' aria-live='assertive' aria-atomic='true' id='toast_aviso' data-bs-delay='4000'>
                <div class='toast-header'>
                    <svg class='bd-placeholder-img rounded me-2' width='20' height='20' xmlns='http://www.w3.org/2000/svg'
                        aria-hidden='true' preserveAspectRatio='xMidYMid slice' focusable='false'>
                        <rect width='100%' height='100%' fill='#007aff'></rect>
                    </svg>
                    <strong class='me-auto'>Aviso</strong>
                    <small>Fechando em <b id='temporizador'></b></small>
                    <a href ='../Imagem-multi-no-BD/index.php'>
                        <button type='button' class='btn-close' data-bs-dismiss='toast' aria-label='Fechar'></button>
                    </a>
                </div>
                <div class='toast-body text-center'>
                    $mensagem
                </div>
            </div>
        </div>
    ";
    }

    ?>

    <div class="container my-3 shadow-lg p-3 mb-5 bg-secondary text-white rounded">
        <div class="row">
            <div class="col-6">
                <h1 class="text-center font-weight-bold">Adicionar Arquivos</h1>
                <!--form com possibilidade de colocar várias imagens, com o multipart/form-data-->
                <form method="POST" enctype="multipart/form-data" action="">
                    <div class="input-group mb-3">
                        <!--input multiplo e name array-->
                        <input multiple name="arquivo[]" type="file" class="form-control" id="arquivo"
                            aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                    </div>
                    <!--botão para enviar o form-->
                    <button class="btn btn-primary btn-lg w-100" name="upload" type="submit">Enviar arquivo</button>
                </form>
            </div>
            <div class="col-6">
                <h1 class="text-center font-weight-bold">Preview</h1>
                <!--Preview da Imagem-->
                <div id="img-container" class="text-center">
                    <img id="preview" alt="Preview" src="" height="100">
                </div>
            </div>
        </div>
    </div>

    <div class="container my-3 shadow-lg p-3 mb-5 bg-light text-dark rounded">

        <h1 class="text-center font-weight-bold">Lista de Arquivos</h1>

        <table class="table table-striped w-100" id="datatable">
            <thead>
                <th class="th-sm">id</th>
                <th class="th-sm">preview</th>
                <th class="th-sm">Arquivo</th>
                <th class="th-sm">Data de envio</th>
                <th class="th-sm"></th>
            </thead>
            <tbody>
                <?php
                //$arquivo recebe os dados da chamada
                while ($arquivo = $sql_query->fetch_assoc()) {
                    ?>
                    <tr>
                        <td>
                            <?php
                            //mostra o id
                            echo $arquivo['id'];
                            ?>
                        </td>
                        <td><!--mostra a img com o src de acordo com o caminho-->
                            <img width="auto" height="75" src="<?php echo $arquivo['path']; ?>">
                        </td>
                        <td><!--mostra o caminho e o nome da imagem-->
                            <a target="_blank" href="<?php echo $arquivo['path']; ?>"><?php echo $arquivo['nome']; ?></a>
                        </td>
                        <td><!--estiliza e mostra a data-->
                            <?php echo date("d/m/Y H:i", strtotime($arquivo['data_upload'])); ?>
                        </td>
                        <td><!--excluir imagem a depender do id-->
                            <a href="index.php?deletar=<?php echo $arquivo['id']; ?>">
                                <input class="btn btn-danger" type="button" value="deletar">
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>


    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"
        integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS"
        crossorigin="anonymous"></script>

    <!--DataTable-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>

    <script>

        //script para colocar DataTable nas tabelas
        $(document).ready(function () {
            $('#datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Portuguese-Brasil.json"
                }
            });
        });
    </script>

    <script>
        //Ter uma Preview da Imagem
        function readImage() {
            if (this.files && this.files[0]) {
                var file = new FileReader();
                file.onload = function (e) {
                    document.getElementById("preview").src = e.target.result;
                };
                file.readAsDataURL(this.files[0]);
            }
        }

        document.getElementById("arquivo").addEventListener("change", readImage, false);
    </script>

    <script>
        //inicar o toast
        const toastLiveExample = document.getElementById('toast_aviso')
        const toast = new bootstrap.Toast(toastLiveExample)
        toast.show()
    </script>

    <script>
        //Temporizador de 4 segundos mostrado na notificação, no id #temporizador
        var temporizador = document.getElementById('temporizador');

        var ativerIntervalo = function () {
            temporizador.innerHTML = 4;
            var intervalo = setInterval(function () {
                var novoValor = parseInt(temporizador.innerHTML, 10) - 1;
                temporizador.innerHTML = novoValor;

                if (novoValor === 0) {
                    clearInterval(intervalo);
                    setTimeout(ativerIntervalo, 3000);
                }
            }, 1000);
        };
        ativerIntervalo();
    </script>

    <?php
    //se houver uma notificão, volta para pagina inicial após 4 segundos
    if (isset($resultado)) {
        echo "
            <script>
                // Redireciona o usuário para a página de antes após cinco segundos
                setTimeout(function () {
                    window.location.href = '../Imagem-multi-no-BD/index.php';
                }, 4000);
            </script>
            ";
    }

    ?>
</body>

</html>