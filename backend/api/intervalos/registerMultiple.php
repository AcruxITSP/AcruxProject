<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../utils/time.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Intervalo.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum IntervaloRegisterError : string
{
    case MISSING_PERMISSIONS = "MATERIA_REGISTER_MISSING_PERMISSIONS";
}

class DatosCreacionIntervalo
{
    public int $numero;
    public int $horaInicioUnix;
    public int $horaFinalUnix;

    public function __construct(int $numero, int $horaInicioUnix, int $horaFinalUnix)
    {
        $this->numero = $numero;
        $this->horaInicioUnix = $horaInicioUnix;
        $this->horaFinalUnix = $horaFinalUnix;
    }
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}
// TODO: APLICAR LUEGO DE TERMINAR REGISTERS
// if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");
$con = connectDb();

// TODO: VALIDAR
$horaEntrada = $_POST["horaEntrada"];
$horaEntradaUnix = DateTime::createFromFormat("G:i", $horaEntrada)->getTimestamp();

$horaCierre = $_POST['horaCierre'];
$horaCierreUnix = DateTime::createFromFormat("G:i", $horaCierre)->getTimestamp();

$duracionHoraMinutos = (int)($_POST['duracionHoraMinutos'] ?? 0);
$duracionRecreoMinutos = (int)($_POST['duracionRecreoMinutos'] ?? 0);

$intervalos = [];
$currentTimeUnix = $horaEntradaUnix;
$horaActual = 1;
while($currentTimeUnix < $horaCierreUnix)
{
    $inicioIntervaloUnix = $currentTimeUnix;

    $currentTimeUnix = UnixTimeHelper::addMinutes($currentTimeUnix, $duracionHoraMinutos);
    $finalIntervaloUnix = $currentTimeUnix;

    $intervalos[] = new DatosCreacionIntervalo($horaActual, $inicioIntervaloUnix, $finalIntervaloUnix);
    $currentTimeUnix = UnixTimeHelper::addMinutes($currentTimeUnix, $duracionRecreoMinutos);
    ++$horaActual;
}

$deleteIntervalosResult = SQL::actionQuery($con, "DELETE FROM intervalo", "");
if($deleteIntervalosResult !== true) Respuestas::enviarError($deleteIntervalosResult);

foreach($intervalos as $intervalo)
{
    $inicioIntervaloSql = UnixTimeHelper::toMySQLTime($intervalo->horaInicioUnix);
    $finalIntervaloSql = UnixTimeHelper::toMySQLTime($intervalo->horaFinalUnix);
    $numeroHora = $intervalo->numero;

    $createIntervaloResult = Intervalo::create($con, $numeroHora, $inicioIntervaloSql, $finalIntervaloSql);
    if(!($createIntervaloResult instanceof Intervalo)) Respuestas::enviarError($createIntervaloResult);
}

Respuestas::enviarOk();
?>