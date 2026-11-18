using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Mappings;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Services;

public sealed class PacienteService(IEntityRepository<Paciente> pacientes,
    IEntityRepository<Consulta> consultas, IEntityRepository<Orcamento> orcamentos) : IPacienteService
{
    public async Task<List<PacienteResponseDto>> GetTodosAsync() =>
        (await pacientes.ListarAsync()).Select(p => p.ToDto()).ToList();

    public async Task<PacienteResponseDto?> GetPorIdAsync(int id) =>
        (await BuscarEntidadeAsync(id))?.ToDto();

    private Task<Paciente?> BuscarEntidadeAsync(int id) => pacientes.BuscarAsync(p => p.IdPaciente == id);

    public async Task<PacienteResponseDto> CriarAsync(PacienteCreateDto dto)
    {
        var paciente = dto.ToEntity();
        if (await pacientes.ExisteAsync(p => p.Cpf == paciente.Cpf))
            throw new BusinessException("CPF já cadastrado.", 409);
        pacientes.Adicionar(paciente);
        await pacientes.SalvarAsync();
        return paciente.ToDto();
    }

    public async Task<PacienteResponseDto?> AtualizarAsync(int id, PacienteUpdateDto dto)
    {
        var paciente = await BuscarEntidadeAsync(id);
        if (paciente is null) return null;
        dto.AplicarEm(paciente);
        await pacientes.SalvarAsync();
        return paciente.ToDto();
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var paciente = await BuscarEntidadeAsync(id);
        if (paciente is null) return false;
        if (await consultas.ExisteAsync(c => c.IdPaciente == id) || await orcamentos.ExisteAsync(o => o.IdPaciente == id))
            throw new BusinessException("Paciente possui consultas ou orçamentos vinculados e não pode ser excluído.");
        pacientes.Remover(paciente);
        await pacientes.SalvarAsync();
        return true;
    }
}
