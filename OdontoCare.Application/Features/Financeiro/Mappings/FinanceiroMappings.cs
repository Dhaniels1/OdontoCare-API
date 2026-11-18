using OdontoCare.Application.Features.Financeiro.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Financeiro.Mappings;

public static class FinanceiroMappings
{
    public static ProcedimentoDetalheDto ToDto(this Procedimento p) =>
            new(p.IdProcedimento, p.NomeProcedimento, p.Descricao, p.ValorProcedimento);
}
