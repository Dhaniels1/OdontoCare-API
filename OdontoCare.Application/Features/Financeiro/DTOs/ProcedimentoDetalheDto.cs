

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed record ProcedimentoDetalheDto(int IdProcedimento, string NomeProcedimento,
    string? Descricao, decimal? ValorProcedimento);
