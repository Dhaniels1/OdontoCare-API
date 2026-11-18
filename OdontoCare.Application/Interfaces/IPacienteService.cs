using OdontoCare.Application.DTOs;

namespace OdontoCare.Application.Interfaces;

public interface IPacienteService
{
    Task<List<PacienteResponseDto>> GetTodosAsync(int? dentistaId = null);
    Task<PacienteResponseDto?> GetPorIdAsync(int id, int? dentistaId = null);
    Task<PacienteProntuarioDto?> GetProntuarioAsync(int id, int? dentistaId = null);
    Task<PacienteResponseDto> CriarAsync(PacienteCreateDto dto, bool podeEditarProntuario);
    Task<PacienteResponseDto?> AtualizarAsync(int id, PacienteUpdateDto dto, bool podeEditarProntuario);
    Task<bool> ExcluirAsync(int id);
}
