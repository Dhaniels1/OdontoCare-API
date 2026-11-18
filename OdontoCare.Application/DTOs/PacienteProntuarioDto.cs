namespace OdontoCare.Application.DTOs;

// Só pode ser retornado por rota clínica autorizada.
public sealed class PacienteProntuarioDto
{
    public int IdPaciente { get; set; }
    public string? HistoricoOdontologico { get; set; }
    public string? Alergias { get; set; }
    public string? Exames { get; set; }
    public string? TratamentosAnteriores { get; set; }
    public string? ProntuarioClinicoDigital { get; set; }
}
