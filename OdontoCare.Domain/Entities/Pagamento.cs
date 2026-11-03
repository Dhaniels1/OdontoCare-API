namespace OdontoCare.Domain.Entities;
public sealed class Pagamento
{
public int IdPagamento { get; set; }
public decimal ValorTotal { get; set; }
public string? FormaPagamento { get; set; }
public string? StatusPagamento { get; set; }
public DateTime? DataPagamento { get; set; }
public int IdConsulta { get; set; }
}
