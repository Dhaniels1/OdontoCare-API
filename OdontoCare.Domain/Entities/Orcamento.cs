namespace OdontoCare.Domain.Entities;
public sealed class Orcamento
{
public int IdOrcamento { get; set; }
public decimal ValorTotal { get; set; }
public DateTime DataOrcamento { get; set; }
public string? StatusOrcamento { get; set; }
public string? Observacoes { get; set; }
public int IdPaciente { get; set; }
}
