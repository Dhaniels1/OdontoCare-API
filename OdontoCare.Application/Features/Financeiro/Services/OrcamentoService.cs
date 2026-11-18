using OdontoCare.Application.Common.Exceptions;
using OdontoCare.Application.Common.Interfaces;
using OdontoCare.Application.Features.Financeiro.DTOs;
using OdontoCare.Application.Features.Financeiro.Interfaces;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Financeiro.Services;

public sealed class OrcamentoService(IEntityRepository<Orcamento> orcamentos,
    IEntityRepository<Paciente> pacientes) : IOrcamentoService
{
    private static OrcamentoResponseDto Map(Orcamento o, string nome) => new(o.IdOrcamento, o.ValorTotal,
        o.DataOrcamento, o.StatusOrcamento, o.Observacoes, o.IdPaciente, nome);

    public async Task<List<OrcamentoResponseDto>> ListarAsync()
    {
        var nomes = (await pacientes.ListarAsync()).ToDictionary(p => p.IdPaciente, p => p.Nome);
        return (await orcamentos.ListarAsync()).OrderByDescending(o => o.DataOrcamento)
            .Select(o => Map(o, nomes.GetValueOrDefault(o.IdPaciente, ""))).ToList();
    }

    public async Task<OrcamentoResponseDto?> BuscarAsync(int id)
    {
        var o = await orcamentos.BuscarAsync(x => x.IdOrcamento == id);
        return o is null ? null : Map(o, (await pacientes.BuscarAsync(p => p.IdPaciente == o.IdPaciente))?.Nome ?? "");
    }

    public async Task<OrcamentoResponseDto?> GravarAsync(int? id, OrcamentoGravarDto dto)
    {
        var o = id.HasValue ? await orcamentos.BuscarAsync(x => x.IdOrcamento == id) : new Orcamento();
        if (o is null) return null;
        var p = await pacientes.BuscarAsync(x => x.IdPaciente == dto.IdPaciente);
        if (p is null) throw new BusinessException("Paciente informado não existe.", 400);
        o.ValorTotal = dto.ValorTotal!.Value; o.DataOrcamento = dto.DataOrcamento!.Value;
        o.StatusOrcamento = dto.StatusOrcamento; o.Observacoes = dto.Observacoes?.Trim(); o.IdPaciente = dto.IdPaciente;
        if (!id.HasValue) orcamentos.Adicionar(o);
        await orcamentos.SalvarAsync();
        return Map(o, p.Nome);
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var o = await orcamentos.BuscarAsync(x => x.IdOrcamento == id);
        if (o is null) return false;
        orcamentos.Remover(o);
        await orcamentos.SalvarAsync();
        return true;
    }
}
