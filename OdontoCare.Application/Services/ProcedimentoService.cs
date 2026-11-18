using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Mappings;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Services;

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
