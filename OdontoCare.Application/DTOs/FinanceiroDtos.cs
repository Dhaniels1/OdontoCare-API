using System.ComponentModel.DataAnnotations;
namespace OdontoCare.Application.DTOs;
public sealed class DinheiroAttribute : ValidationAttribute
{
    public override bool IsValid(object? value) => value is decimal d && d >= 0 && d <= 99999999.99m && decimal.Round(d, 2) == d;
    public DinheiroAttribute() => ErrorMessage = "Informe valor entre 0 e 99999999,99 com no máximo duas casas decimais.";
}
public sealed class DataFinanceiraAttribute : ValidationAttribute
{
    public override bool IsValid(object? value) => value is DateTime d && d.Year >= 1000 && d.TimeOfDay == TimeSpan.Zero;
    public DataFinanceiraAttribute() => ErrorMessage = "Informe uma data válida, sem horário.";
}
public sealed class ProcedimentoGravarDto
{
    [Required, StringLength(200)] public string NomeProcedimento { get; set; } = "";
    [StringLength(16000)] public string? Descricao { get; set; }
    [Required, Dinheiro] public decimal? ValorProcedimento { get; set; }
}
public sealed class OrcamentoGravarDto
{
    [Required, Dinheiro] public decimal? ValorTotal { get; set; }
    [Required, DataFinanceira] public DateTime? DataOrcamento { get; set; }
    [Required, RegularExpression("^(Pendente|Aprovado|Cancelado)$")] public string StatusOrcamento { get; set; } = "";
    [StringLength(16000)] public string? Observacoes { get; set; }
    [Range(1, int.MaxValue)] public int IdPaciente { get; set; }
}
public sealed class PagamentoGravarDto
{
    [Required, Dinheiro] public decimal? ValorTotal { get; set; }
    [Required, RegularExpression("^(Pix|Dinheiro|Cartão de Débito|Cartão de Crédito)$")] public string FormaPagamento { get; set; } = "";
    [Required, RegularExpression("^(Pendente|Pago|Cancelado)$")] public string StatusPagamento { get; set; } = "";
    [Required, DataFinanceira] public DateTime? DataPagamento { get; set; }
    [Range(1, int.MaxValue)] public int IdConsulta { get; set; }
}
public sealed record OrcamentoResponseDto(int IdOrcamento, decimal ValorTotal, DateTime DataOrcamento,
    string? StatusOrcamento, string? Observacoes, int IdPaciente, string NomePaciente);
public sealed record PagamentoResponseDto(int IdPagamento, decimal ValorTotal, string? FormaPagamento,
    string? StatusPagamento, DateTime? DataPagamento, int IdConsulta, string NomePaciente, string NomeProcedimento);
public sealed record ReciboDto(PagamentoResponseDto Pagamento, string Cpf);
public sealed record ProcedimentoResumoDto(string NomeProcedimento, int Total);
public sealed record PainelDto(int TotalPacientes, int TotalConsultas, int TotalProcedimentos, int TotalPagamentos,
    int TotalMateriais, int ConsultasHoje, decimal ValorRecebido, decimal ValorPendente, decimal ValorCancelado,
    List<OdontoCare.Domain.Entities.Material> MateriaisBaixoEstoque,
    List<ProcedimentoResumoDto> DadosProcedimentos, List<ConsultaListaDto> ProximasConsultas);
