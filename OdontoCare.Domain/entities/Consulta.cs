
namespace OdontoCare.Domain.Entities;

public class Consulta
{
    public int IdConsulta { get; set; }

    public DateTime DataConsulta { get; set; }

    public TimeSpan HorarioConsulta { get; set; }

    public string? StatusConsulta { get; set; }

    public string? Observacoes { get; set; }

    public int IdUsuario { get; set; }

    public int IdPaciente { get; set; }

    public int IdProcedimento { get; set; }
}
