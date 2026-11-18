using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Mappings;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Services;

public sealed class PacienteService(IEntityRepository<Paciente> pacientes,
    IEntityRepository<Consulta> consultas, IEntityRepository<Orcamento> orcamentos) : IPacienteService
{
    public async Task<List<PacienteResponseDto>> GetTodosAsync(int? dentistaId = null)
    {
        if (!dentistaId.HasValue)
            return (await pacientes.ListarAsync()).Select(p => p.ToDto()).ToList();
        var ids = (await consultas.ListarAsync(c => c.IdUsuario == dentistaId.Value))
            .Select(c => c.IdPaciente).ToHashSet();
        return (await pacientes.ListarAsync()).Where(p => ids.Contains(p.IdPaciente))
            .Select(p => p.ToDto()).ToList();
    }

    public async Task<PacienteResponseDto?> GetPorIdAsync(int id, int? dentistaId = null)
    {
        if (!await PodeAcessarPacienteAsync(id, dentistaId)) return null;
        return (await BuscarEntidadeAsync(id))?.ToDto();
    }

    // A secretária não possui uma rota que devolva campos clínicos.
    public async Task<PacienteProntuarioDto?> GetProntuarioAsync(int id, int? dentistaId = null)
    {
        if (!await PodeAcessarPacienteAsync(id, dentistaId)) return null;
        return (await BuscarEntidadeAsync(id))?.ToProntuarioDto();
    }

    private Task<bool> PodeAcessarPacienteAsync(int id, int? dentistaId) =>
        dentistaId.HasValue
            ? consultas.ExisteAsync(c => c.IdUsuario == dentistaId.Value && c.IdPaciente == id)
            : Task.FromResult(true);

    private Task<Paciente?> BuscarEntidadeAsync(int id) =>
        pacientes.BuscarAsync(p => p.IdPaciente == id);

    private static bool ContemCamposClinicos(PacienteCreateDto d) =>
        d.HistoricoOdontologico is not null || d.Alergias is not null ||
        d.Exames is not null || d.TratamentosAnteriores is not null ||
        d.ProntuarioClinicoDigital is not null;

    private static bool ContemCamposClinicos(PacienteUpdateDto d) =>
        d.HistoricoOdontologico is not null || d.Alergias is not null ||
        d.Exames is not null || d.TratamentosAnteriores is not null ||
        d.ProntuarioClinicoDigital is not null;

    public async Task<PacienteResponseDto> CriarAsync(PacienteCreateDto dto, bool podeEditarProntuario)
    {
        if (!podeEditarProntuario && ContemCamposClinicos(dto))
            throw new BusinessException("Seu perfil não pode preencher dados clínicos.", 403);
        var paciente = dto.ToEntity();
        if (await pacientes.ExisteAsync(p => p.Cpf == paciente.Cpf))
            throw new BusinessException("CPF já cadastrado.", 409);
        pacientes.Adicionar(paciente);
        await pacientes.SalvarAsync();
        return paciente.ToDto();
    }

    public async Task<PacienteResponseDto?> AtualizarAsync(int id, PacienteUpdateDto dto, bool podeEditarProntuario)
    {
        if (!podeEditarProntuario && ContemCamposClinicos(dto))
            throw new BusinessException("Seu perfil não pode modificar dados clínicos.", 403);
        var paciente = await BuscarEntidadeAsync(id);
        if (paciente is null) return null;
        dto.AplicarEm(paciente, podeEditarProntuario);
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
