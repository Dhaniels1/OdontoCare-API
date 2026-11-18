using OdontoCare.Application.Common.Exceptions;
using OdontoCare.Application.Common.Interfaces;
using OdontoCare.Application.Features.Consultas.Mappings;
using OdontoCare.Application.Features.Estoque.Mappings;
using OdontoCare.Application.Features.Financeiro.DTOs;
using OdontoCare.Application.Features.Financeiro.Interfaces;
using OdontoCare.Application.Features.Financeiro.Mappings;
using OdontoCare.Application.Features.Pacientes.Mappings;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Financeiro.Services;

public sealed class ProcedimentoService(IEntityRepository<Procedimento> procedimentos,
    IEntityRepository<Consulta> consultas) : IProcedimentoService
{
    public async Task<List<ProcedimentoDetalheDto>> ListarAsync() =>
        (await procedimentos.ListarAsync()).OrderBy(x => x.NomeProcedimento).Select(x => x.ToDto()).ToList();

    public async Task<ProcedimentoDetalheDto?> BuscarAsync(int id) => (await BuscarEntidadeAsync(id))?.ToDto();

    private Task<Procedimento?> BuscarEntidadeAsync(int id) => procedimentos.BuscarAsync(x => x.IdProcedimento == id);

    public async Task<ProcedimentoDetalheDto?> GravarAsync(int? id, ProcedimentoGravarDto dto)
    {
        var p = id.HasValue ? await BuscarEntidadeAsync(id.Value) : new Procedimento();
        if (p is null) return null;
        p.NomeProcedimento = dto.NomeProcedimento.Trim();
        p.Descricao = dto.Descricao?.Trim();
        p.ValorProcedimento = dto.ValorProcedimento;
        if (!id.HasValue) procedimentos.Adicionar(p);
        await procedimentos.SalvarAsync();
        return p.ToDto();
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var p = await BuscarEntidadeAsync(id);
        if (p is null) return false;
        if (await consultas.ExisteAsync(c => c.IdProcedimento == id))
            throw new BusinessException("Procedimento possui consultas vinculadas.");
        procedimentos.Remover(p);
        await procedimentos.SalvarAsync();
        return true;
    }
}
