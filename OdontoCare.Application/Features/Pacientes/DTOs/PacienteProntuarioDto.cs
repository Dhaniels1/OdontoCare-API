

namespace OdontoCare.Application.Features.Pacientes.DTOs;

public sealed class PacienteProntuarioDto
{
    public int IdPaciente { get; set; }
    public string? HistoricoOdontologico { get; set; }
    public string? Alergias { get; set; }
    public string? Exames { get; set; }
    public string? TratamentosAnteriores { get; set; }
    public string? ProntuarioClinicoDigital { get; set; }
}
