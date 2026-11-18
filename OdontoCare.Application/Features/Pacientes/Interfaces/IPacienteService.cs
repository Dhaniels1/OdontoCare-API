using OdontoCare.Application.Features.Pacientes.DTOs;

namespace OdontoCare.Application.Features.Pacientes.Interfaces;

public interface IPacienteService
{
    Task<List<PacienteResponseDto>> GetTodosAsync(int? dentistaId = null);
    Task<PacienteResponseDto?> GetPorIdAsync(int id, int? dentistaId = null);
    Task<PacienteProntuarioDto?> GetProntuarioAsync(int id, int? dentistaId = null);
    Task<PacienteResponseDto> CriarAsync(PacienteCreateDto dto, bool podeEditarProntuario);
    Task<PacienteResponseDto?> AtualizarAsync(int id, PacienteUpdateDto dto, bool podeEditarProntuario);
    Task<bool> ExcluirAsync(int id);
}
