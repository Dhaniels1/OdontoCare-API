namespace OdontoCare.Domain.Entities;
public sealed class Procedimento
{
    public int IdProcedimento { get; set; }
    public string NomeProcedimento { get; set; } = "";
    public string? Descricao { get; set; }
    public decimal? ValorProcedimento { get; set; }
}
